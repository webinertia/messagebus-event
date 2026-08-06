# Usage Examples

## Listening for Command events

The default pipeline (see [`ConfigProvider::getPipeline()`](api-reference.md#configprovider)) dispatches:

1. `Command\CommandPreHandleEvent` — before the command's handler runs.
2. `Command\CommandPostHandleEvent` — after the handler returns a `CommandResultInterface`.

Register a listener under the `listeners` config key, keyed by the event class:

```php
use Webware\MessageBus\Event\Command\CommandPostHandleEvent;
use Webware\MessageBus\Event\EventInterface;
use Webware\MessageBus\Event\ListenerInterface;

final class LogCommandCompletion implements ListenerInterface
{
    public function __invoke(EventInterface $event): void
    {
        if (! $event instanceof CommandPostHandleEvent) {
            return;
        }

        printf(
            "Command %s completed with status: %s\n",
            $event->getCommand()::class,
            $event->getResult()->getStatus()->name,
        );
    }
}
```

```php
return [
    'listeners' => [
        CommandPostHandleEvent::class => [
            LogCommandCompletion::class,
        ],
    ],
];
```

`ListenerProviderAggregateFactory` resolves `LogCommandCompletion` from the container if it's registered
there (e.g. as an invokable or via a factory); otherwise it's treated as a plain callable/class-string.

## Ordering listeners by priority

Use the shape form (`['listener' => ..., 'priority' => ...]`) when multiple listeners are attached to the
same event and must run in a specific order. Higher priority values run first:

```php
return [
    'listeners' => [
        CommandPostHandleEvent::class => [
            ['listener' => AuditListener::class, 'priority' => 10],
            ['listener' => LogCommandCompletion::class, 'priority' => 0],
        ],
    ],
];
```

## Opting in to Query events

`Middleware\QueryPreHandleMiddleware` and `Middleware\QueryPostHandleMiddleware` are registered in the
container by this package, but are **not** added to the default `middleware_pipeline` — only Command events
are wired out of the box. Add them yourself, choosing priorities relative to your other middleware (e.g.
`webware/message-bus`'s own `MessageHandlerMiddleware`, wired at priority `1`):

```php
use Webware\MessageBus\ConfigProvider as BusProvider;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;
use Webware\MessageBus\MessageBusInterface;

return [
    MessageBusInterface::class => [
        BusProvider::MIDDLEWARE_PIPELINE_KEY => [
            ['middleware' => QueryPreHandleMiddleware::class, 'priority' => 100],
            ['middleware' => QueryPostHandleMiddleware::class, 'priority' => -100],
        ],
    ],
];
```

Merging this config on top of the package's own `ConfigProvider` output (later providers win when merged
with `array_merge_recursive`) adds Query events alongside the default Command ones.

## Dispatching a custom event alongside the result

Both `CommandPostHandleMiddleware` and `QueryPostHandleMiddleware` also check whether the result implements
`EventAwareInterface`; if so, and an event has been attached to it, that event is dispatched too — in
addition to the built-in `*PostHandleEvent`.

Always return the `CommandResult`/`QueryResult` value objects provided by `webware/message-bus` from your
handlers — don't write a custom result class. Those classes are `final` precisely so results stay
normalized and consistent across the application. Neither implements `EventAwareInterface`, so this hook
simply doesn't apply to them; if a handler needs to dispatch an additional domain event, dispatch it
directly from the handler using the `EventDispatcherInterface` instead of attaching it to the result:

```php
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\Event\Event;

final class CreateUserHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function handle(CreateUser $command): CommandResult
    {
        $userId = /* ... create the user ... */;

        $this->eventDispatcher->dispatch(new Event(name: 'user.created', target: $command, params: ['id' => $userId]));

        return new CommandResult($command, $status, $userId);
    }
}
```

## Stopping propagation

`Event` implements `EventPropagationInterface` via `EventPropagationTrait`. A listener can stop later
listeners for the same event from running:

```php
use Webware\MessageBus\Event\EventInterface;
use Webware\MessageBus\Event\EventPropagationInterface;
use Webware\MessageBus\Event\ListenerInterface;

final class HaltOnError implements ListenerInterface
{
    public function __invoke(EventInterface $event): void
    {
        if ($event instanceof EventPropagationInterface && /* some condition */ false) {
            $event->stopPropagation();
        }
    }
}
```

Note that `phly/phly-event-dispatcher`'s `EventDispatcher` must check `isPropagationStopped()` between
listener invocations for this to have an effect — see that package's own documentation for details.
