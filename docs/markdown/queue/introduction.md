# Use queue with Ark

Is is common to use a queue to do asynchronous tasks. 
Ark defined a standard for task handler and daemon which might run in both synchronous and asynchronous style.

> Note: The asynchronous style is based on PCNTL, so that Windows is not supported. 

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

## Advanced Design

Here list some strategics to help for your own advanced queue realization.

### Use runtime command

The delegate supports several methods to make the loop flexible.
Now three are there:
* `shouldTerminate`, if this method returns true, the loop would be terminated and run `whenLoopTerminates` before exit.
* `isRunnable`, if this method returns false, the loop would run `whenLoopShouldNotRun` and go to next turn.
* `shouldWaitForAnyWorkerDone`, if this method return true in `SINGLE_POOLED` style, the loop would be paused and waiting for a termination of any child process.

