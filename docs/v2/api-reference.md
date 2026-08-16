# API Reference

All classes live under the `Webware\MessageBus\Event` namespace (PSR-4 root: `src/`).

## Core Event Types

### `EventInterface`

```php
interface EventInterface
{
    public function getName(): string;
    public function getParam(string $name, mixed $default = null): mixed;
    /** @return array<string, mixed> */
    public function getParams(): array;
    public function getTarget(): ?object;
    public function setName(string $name): void;
    public function setParam(string $name, mixed $value): void;
    /** @param array<string, mixed> $params */
    public function setParams(array $params): void;
    public function setTarget(object $target): void;
}
```

### `Event`

Default `EventInterface` implementation. Also implements `EventPropagationInterface` via
`EventPropagationTrait`.

```php
class Event implements EventInterface, EventPropagationInterface
{
    /** @param array<string, mixed> $params */
    public function __construct(
        ?string $name = null,
        ?object $target = null,
        array $params = [],
    ) {}
}
```

If `$name` is omitted, `getName()` falls back to `static::class` — useful for the `Command\*`/`Query\*`
subclasses below, which don't set a name explicitly.

### `EventPropagationInterface` / `EventPropagationTrait`

```php
interface EventPropagationInterface
{
    public function isPropagationStopped(): bool;
    public function stopPropagation(): void;
}

trait EventPropagationTrait
{
    protected bool $propagationStopped = false;
    public function isPropagationStopped(): bool;
    public function stopPropagation(bool $flag = true): void;
}
```

The trait's `stopPropagation()` accepts an optional `bool $flag = true`, letting callers re-enable
propagation (`stopPropagation(false)`) even though the interface only requires a no-argument method.

### `EventAwareInterface` / `EventAwareTrait`

Lets a message or result object carry a domain event that the post-handle middleware can dispatch
alongside the built-in `*PostHandleEvent`.

```php
interface EventAwareInterface
{
    public function getEvent(): ?EventInterface;
    public function setEvent(EventInterface $event): void;
}

trait EventAwareTrait
{
    public function getEvent(): ?EventInterface;
    public function setEvent(EventInterface $event): void;
}
```

### `ListenerInterface`

Marker/callable-shape interface for object listeners (as opposed to plain closures or `callable` strings).

```php
interface ListenerInterface
{
    public function __invoke(EventInterface $event): void;
}
```

## Command Events

`Webware\MessageBus\Event\Command`:

- **`CommandPreHandleEvent`** — `extends Event`. Constructed with a `CommandInterface $command`
  (`getTarget()` returns the command). `getCommand(): CommandInterface`.
- **`CommandPostHandleEvent`** — `extends Event`. Constructed with a `CommandResultInterface $result`
  (`getTarget()` returns the result). `getCommand(): CommandInterface` (delegates to
  `$result->getCommand()`), `getResult(): CommandResultInterface`.

## Query Events

`Webware\MessageBus\Event\Query`:

- **`QueryPreHandleEvent`** — `extends Event`. Constructed with a `QueryInterface $query`.
  `getQuery(): QueryInterface`.
- **`QueryPostHandleEvent`** — `extends Event`. Constructed with a `QueryResultInterface $result`.
  `getQuery(): QueryInterface` (delegates to `$result->getQuery()`), `getResult(): QueryResultInterface`.

## Middleware

`Webware\MessageBus\Event\Middleware`, all implementing `Webware\MessageBus\MiddlewareInterface`.

> **v2 change:** `webware/message-bus` v2 changed `MiddlewareInterface::process()` to take a
> `PipelineHandlerInterface $next` instead of the v1 `MessageHandlerInterface $handler`.
> `PipelineHandlerInterface` is `@internal` and is meant to be type-hinted, not implemented — the
> pipeline supplies the implementation (`Next` / `Handler\EmptyPipelineHandler`).

| Class | Dispatches | Behavior |
|---|---|---|
| `CommandPreHandleMiddleware` | `CommandPreHandleEvent` | If `$message instanceof CommandInterface`, dispatches then calls `$next->handle($message)`. |
| `CommandPostHandleMiddleware` | `CommandPostHandleEvent` | If `$message instanceof EventAwareInterface` with a non-null event, dispatches that event first. If `$message instanceof CommandResultInterface`, dispatches `CommandPostHandleEvent` and returns `$message` directly (does not call `$next->handle()`). Otherwise forwards to `$next->handle($message)`. |
| `QueryPreHandleMiddleware` | `QueryPreHandleEvent` | Same pattern as `CommandPreHandleMiddleware`, gated on `QueryInterface`. |
| `QueryPostHandleMiddleware` | `QueryPostHandleEvent` | Same pattern as `CommandPostHandleMiddleware`, gated on `QueryResultInterface`. |

Each middleware's constructor takes a single `Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher`.

Because `webware/message-bus`'s `MessageHandlerMiddleware` forwards the handler's `ResultInterface` back
into the pipeline as the next "message" (`$next->handle($result)`), a `*PostHandleMiddleware` placed
*after* it in priority order receives the result — not the original command/query — as `$message`. This is
why the pipeline priorities matter: pre-handle middleware needs a higher priority than the message handler,
post-handle middleware needs a lower one.

> **v2 note:** `MessageHandlerMiddleware` resolves the handler method via a `StrategyInterface` —
> `HandleStrategy` (default) calls `handle()`, `ClassnameStrategy` calls a method named after the
> message's short class name (`lcfirst`). Handlers must implement the (empty marker)
> `CommandHandlerInterface` or `QueryHandlerInterface`.

> **v2 note:** `CommandResultInterface`/`QueryResultInterface` are `@internal` in `webware/message-bus`
> v2 — they exist for type-hinting only (`CommandResult`/`QueryResult` are `final`), not for
> implementation.

## Container Factories

`Webware\MessageBus\Event\Container`, all `@internal` — wired automatically by `ConfigProvider`, not
intended for direct instantiation by consumers:

- `CommandPreHandleMiddlewareFactory`, `CommandPostHandleMiddlewareFactory`,
  `QueryPreHandleMiddlewareFactory`, `QueryPostHandleMiddlewareFactory` — each resolves
  `Psr\EventDispatcher\EventDispatcherInterface` from the container and constructs the corresponding
  middleware.
- `ListenerProviderAggregateFactory` — builds a `Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate`
  from the `listeners` / `listener_providers` config (see below), attaching a
  `PrioritizedListenerProvider` (for shape entries with a `priority`) and an `AttachableListenerProvider`
  (for everything else) to it, plus any additional listener providers registered under
  `listener_providers`.

## `ConfigProvider`

```php
final readonly class ConfigProvider
{
    public const string LISTENER_KEY = 'listeners';
    public const string LISTENER_PROVIDER_KEY = 'listener_providers';

    public function __invoke(): array;
}
```

Returned config shape:

```php
[
    'dependencies' => [
        'aliases' => [
            Psr\EventDispatcher\EventDispatcherInterface::class => Phly\EventDispatcher\EventDispatcher::class,
            Psr\EventDispatcher\ListenerProviderInterface::class => Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate::class,
        ],
        'factories' => [
            // ListenerProviderAggregate::class and the four *Middleware::class => *Factory::class entries
        ],
    ],
    Webware\MessageBus\MessageBusInterface::class => [
        // BusProvider::MIDDLEWARE_PIPELINE_KEY
        'middleware_pipeline' => [
            ['middleware' => Middleware\CommandPreHandleMiddleware::class, 'priority' => 100],
            ['middleware' => Middleware\CommandPostHandleMiddleware::class, 'priority' => -100],
        ],
    ],
    'listeners' => [],
    'listener_providers' => [],
]
```

Note the aliases assume `phly/phly-event-dispatcher`'s concrete `EventDispatcher` class — its constructor
requires a `ListenerProviderInterface`, so it needs its own factory registered
(`Phly\EventDispatcher\EventDispatcherFactory`) in any container you assemble; this package does not
register that factory for you (see [Getting Started](getting-started.md)).

### `listeners` config shape

```php
array<class-string, array<int, string|array{listener: callable|class-string, priority?: int}>>
```

Keyed by event class (or any object type dispatched through the event dispatcher). Each entry is either:

- A plain string — a service id to resolve from the container, or (if not registered) a `callable` string
  such as a function name.
- A shape array `['listener' => callable|class-string, 'priority' => int]` — `priority` is optional; when
  present, the listener is attached to the `PrioritizedListenerProvider` (descending priority order), and
  when absent, to the `AttachableListenerProvider` (attach order, deduplicated).

### `listener_providers` config shape

```php
array<class-string>
```

A list of service ids resolving to `Psr\EventDispatcher\ListenerProviderInterface` implementations. Ids
that aren't registered in the container are silently skipped.
