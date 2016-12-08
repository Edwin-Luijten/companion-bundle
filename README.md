# Companion bundle for Mini Symfony Skeleton
This bundle can also be used with the MicroFramework bundle

## Debug tools

### Console commands
- debug:container
- debug:router
- debug:event-dispatcher

### Debug bar (disabled by default)

With the debug bar you can gather information about the following:
- Environment information
- Current route
- Request information
- Queries
- Events
- Timeline

# Configuration

```yaml
mini_symfony:
  debug:
    debugbar:
      enabled: false
      
      # Enable/disable DataCollectors
      collectors:
        events: false
        exceptions: false
        request: true
        routing: true
        phpinfo: true
        kernel: true
        time: true
        memory: true
        queries: false

      # Configure some DataCollectors
      options:
        queries:
          with_params: true # Render SQL with the parameters substituted
          timeline: false # Add the queries to the timeline
          explain:
            enabled: false
            types:
              - SELECT # SELECT, INSERT, UPDATE, DELETE for MySQL 5.6.3+
          hints: true # Show hints for common mistakes
        routing:
          label: true
```

## Query collector
Get performance information about your queries.

### Configuration

1. Enable the query logger for your dbal connection:
```yaml
services:
  dbal_logger:
    class: Doctrine\DBAL\Logging\DebugStack  
  
  dbal_config:
    class: Doctrine\DBAL\Configuration
    calls:
      - [setSQLLogger, ['@dbal_logger']]
  
  dbal:
    class: Doctrine\DBAL\Connection
    factory: ['Doctrine\DBAL\DriverManager', getConnection]
    arguments:
      - '%database%'
      - '@dbal_config'
```

2. Enable extra options for the query collector
```yaml
...
options:
    queries:
      with_params: true # Render SQL with the parameters substituted
      timeline: false # Add the queries to the timeline
      explain:
        enabled: false
        types:
          - SELECT # SELECT, INSERT, UPDATE, DELETE for MySQL 5.6.3+
      hints: true # Show hints for common mistakes
```