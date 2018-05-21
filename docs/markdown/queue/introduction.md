# Use queue with Ark

Is is common to use a queue to do asynchronous tasks. 
Ark defined a standard for task handler and daemon which might run in both synchronous and asynchronous style.

## Quick Start

First of all, you should think about what your tasks are and decide which style you would use.
Simply serial queue runs synchronously while parallel queue runs asynchronously.
The two styles are defined as `QueueDaemon::Style`:

```php
const DAEMON_STYLE_SINGLE_SYNCHRONIZED = "SINGLE_SYNCHRONIZED";
const DAEMON_STYLE_SINGLE_POOLED = "SINGLE_POOLED";
```

Then you should think about what parameters should be configurable.
You might put them into the extended class of `QueueDaemonConfiguration`.

For the daemon of a queue, a delegate, which would be an instance of an extended class of `QueueDaemonDelegate`, is requested.
The instance of configuration class would be used for creating it.
The `QueueDaemon` relies the delegate to handle the whole routine, which runs with `loop` method.