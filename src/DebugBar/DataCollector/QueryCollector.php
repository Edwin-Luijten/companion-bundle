<?php

namespace MiniSymfony\CompanionBundle\DebugBar\DataCollector;


use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Doctrine\DBAL\Connection;

/**
 * Collects data about SQL statements executed with PDO
 */
class QueryCollector extends PDOCollector
{
    protected $timeCollector;
    protected $queries = [];
    protected $renderSqlWithParams = false;
    protected $explainQuery = false;
    protected $explainTypes = ['SELECT']; // ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
    protected $showHints = false;
    protected $reflection = [];

    /**
     * @param TimeDataCollector $timeCollector
     */
    public function __construct(TimeDataCollector $timeCollector = null)
    {
        $this->timeCollector = $timeCollector;
    }

    /**
     * Renders the SQL of traced statements with params embedded
     *
     * @param boolean $enabled
     * @param string $quotationChar NOT USED
     */
    public function setRenderSqlWithParams($enabled = true, $quotationChar = "'")
    {
        $this->renderSqlWithParams = $enabled;
    }

    /**
     * Show or hide the hints in the parameters
     *
     * @param boolean $enabled
     */
    public function setShowHints($enabled = true)
    {
        $this->showHints = $enabled;
    }

    /**
     * Enable/disable the EXPLAIN queries
     *
     * @param  bool $enabled
     * @param  array|null $types Array of types to explain queries (select/insert/update/delete)
     */
    public function setExplainSource($enabled, $types)
    {
        $this->explainQuery = $enabled;
        if($types){
            $this->explainTypes = array_unique($types);
        }
    }
    /**
     *
     * @param string $query
     * @param array $bindings
     * @param float $time
     * @param Connection $connection
     */
    public function addQuery($query, $bindings, $time, $connection)
    {
        $explainResults = [];
        $time = $time / 1000;
        $endTime = microtime(true);
        $startTime = $endTime - $time;
        $hints = $this->performQueryAnalysis($query);

        // Run EXPLAIN on this query (if needed)
        if ($this->explainQuery && preg_match('/^('.implode($this->explainTypes).') /i', $query)) {
            $statement = $connection->prepare('EXPLAIN ' . $query);
            $statement->execute($bindings);
            $explainResults = $statement->fetchAll(\PDO::FETCH_CLASS);
        }

        $bindings = $this->checkBindings($bindings);
        if (!empty($bindings) && $this->renderSqlWithParams) {
            foreach ($bindings as $key => $binding) {
                // This regex matches placeholders only, not the question marks,
                // nested in quotes, while we iterate through the bindings
                // and substitute placeholders by suitable values.
                $regex = is_numeric($key)
                    ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                    : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
                $query = preg_replace($regex, $connection->quote($binding), $query, 1);
            }
        }

        $this->queries[] = [
            'query' => $query,
            'bindings' => $this->escapeBindings($bindings),
            'time' => $time,
            'source' => null,
            'explain' => $explainResults,
            'connection' => $connection->getDatabase(),
            'hints' => $this->showHints ? $hints : null,
        ];
        if ($this->timeCollector !== null) {
            $this->timeCollector->addMeasure($query, $startTime, $endTime);
        }
    }
    /**
     * Check bindings for illegal (non UTF-8) strings, like Binary data.
     *
     * @param $bindings
     * @return mixed
     */
    protected function checkBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            if (is_string($binding) && !mb_check_encoding($binding, 'UTF-8')) {
                $binding = '[BINARY DATA]';
            }
        }
        return $bindings;
    }
    /**
     * Make the bindings safe for outputting.
     *
     * @param array $bindings
     * @return array
     */
    protected function escapeBindings($bindings)
    {
        foreach ($bindings as &$binding) {
            $binding = htmlentities($binding, ENT_QUOTES, 'UTF-8', false);
        }
        return $bindings;
    }
    /**
     * Explainer::performQueryAnalysis()
     *
     * Perform simple regex analysis on the code
     *
     * @package xplain (https://github.com/rap2hpoutre/mysql-xplain-xplain)
     * @author e-doceo
     * @copyright 2014
     * @version $Id$
     * @access public
     * @param string $query
     * @return string
     */
    protected function performQueryAnalysis($query)
    {
        $hints = [];
        if (preg_match('/^\\s*SELECT\\s*`?[a-zA-Z0-9]*`?\\.?\\*/i', $query)) {
            $hints[] = 'Use <code>SELECT *</code> only if you need all columns from table';
        }
        if (preg_match('/ORDER BY RAND()/i', $query)) {
            $hints[] = '<code>ORDER BY RAND()</code> is slow, try to avoid if you can.
				You can <a href="http://stackoverflow.com/questions/2663710/how-does-mysqls-order-by-rand-work" target="_blank">read this</a>
				or <a href="http://stackoverflow.com/questions/1244555/how-can-i-optimize-mysqls-order-by-rand-function" target="_blank">this</a>';
        }
        if (strpos($query, '!=') !== false) {
            $hints[] = 'The <code>!=</code> operator is not standard. Use the <code>&lt;&gt;</code> operator to test for inequality instead.';
        }
        if (stripos($query, 'WHERE') === false && preg_match('/^(SELECT) /i', $query)) {
            $hints[] = 'The <code>SELECT</code> statement has no <code>WHERE</code> clause and could examine many more rows than intended';
        }
        if (preg_match('/LIMIT\\s/i', $query) && stripos($query, 'ORDER BY') === false) {
            $hints[] = '<code>LIMIT</code> without <code>ORDER BY</code> causes non-deterministic results, depending on the query execution plan';
        }
        if (preg_match('/LIKE\\s[\'"](%.*?)[\'"]/i', $query, $matches)) {
            $hints[] = 	'An argument has a leading wildcard character: <code>' . $matches[1]. '</code>.
								The predicate with this argument is not sargable and cannot use an index if one exists.';
        }
        return implode("<br />", $hints);
    }

    /**
     * Reset the queries.
     */
    public function reset()
    {
        $this->queries = [];
    }
    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $totalTime = 0;
        $queries = $this->queries;
        $statements = [];
        foreach ($queries as $query) {
            $totalTime += $query['time'];
            $bindings = $query['bindings'];
            if($query['hints']){
                $bindings['hints'] = $query['hints'];
            }
            $statements[] = [
                'sql' => $this->formatSql($query['query']),
                'params' => (object) $bindings,
                'duration' => $query['time'],
                'duration_str' => $this->formatDuration($query['time']),
                'stmt_id' => $query['source'],
                'connection' => $query['connection'],
            ];
            //Add the results from the explain as new rows
            foreach($query['explain'] as $explain){
                $statements[] = [
                    'sql' => ' - EXPLAIN #' . $explain->id . ': `' . $explain->table . '` (' . $explain->select_type . ')',
                    'params' => $explain,
                    'row_count' => $explain->rows,
                    'stmt_id' => $explain->id,
                ];
            }
        }
        $data = [
            'nb_statements' => count($queries),
            'nb_failed_statements' => 0,
            'accumulated_duration' => $totalTime,
            'accumulated_duration_str' => $this->formatDuration($totalTime),
            'statements' => $statements
        ];
        return $data;
    }
    /**
     * Removes extra spaces at the beginning and end of the SQL query and its lines.
     *
     * @param string $sql
     * @return string
     */
    protected function formatSql($sql)
    {
        return trim(preg_replace("/\s*\n\s*/", "\n", $sql));
    }
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'queries';
    }
    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return [
            "queries" => [
                "icon" => "inbox",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => "queries",
                "default" => "[]"
            ],
            "queries:badge" => [
                "map" => "queries.nb_statements",
                "default" => 0
            ]
        ];
    }
}