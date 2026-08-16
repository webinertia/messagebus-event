# Aligning messagebus-event with message-bus v2

## Goal

Update `webware/messagebus-event` so it works against `webware/message-bus` v2 (`2.0.x`). The bulk of
the work is in the four middleware classes; the rest is dependency-bump + test-double changes.

## Scope of message-bus v2 changes that affect us

The v2 branch reworked the middleware contract and the handler-resolution mechanism. The changes that
touch this package:

| Area | v1 (current vendor) | v2 (`2.0.x`) |
|------|---------------------|--------------|
| `MiddlewareInterface::process()` | `(MessageInterface $message, MessageHandlerInterface $handler): ResultInterface` | `(MessageInterface $message, PipelineHandlerInterface $next): ResultInterface` |
| `MessageHandlerInterface` | declares `handle(MessageInterface): ResultInterface` | **empty** interface, `@internal` |
| `MessageInterface` | marker | **empty** marker, `@internal` |
| Handler method name | always `handle()` | via `StrategyInterface` — `HandleStrategy` → `handle()`, `ClassnameStrategy` → `lcfirst(shortName)` |
| `MessageHandlerMiddleware` | ctor `(resolver)`; calls `resolve()->handle()` then `$handler->handle($result)` | ctor `(resolver, strategy)`; calls `$resolved->{$method}()` then `$next->handle($result)` |
| `ResultInterface` | `@internal`, extends `MessageInterface` | unchanged (still extends `MessageInterface`) |
| `CommandResultInterface` / `QueryResultInterface` | `@api` | **`@internal`** |
| New types | — | `PipelineHandlerInterface`, `Handler\EmptyPipelineHandler`, `StrategyInterface`, `Strategy\HandleStrategy`, `Strategy\ClassnameStrategy` |

The result-forwarding behavior is the **same** in both versions: `MessageHandlerMiddleware` forwards the
handler's result down the pipeline as the next "message". So post-handle middleware still receives the
result (a `CommandResultInterface`/`QueryResultInterface`), not the original message.

## Work items

### 1. Migrate the four middleware (core change)

Files:
- `src/Middleware/CommandPreHandleMiddleware.php`
- `src/Middleware/CommandPostHandleMiddleware.php`
- `src/Middleware/QueryPreHandleMiddleware.php`
- `src/Middleware/QueryPostHandleMiddleware.php`

Each does the same mechanical change:

```diff
- use Webware\MessageBus\MessageHandlerInterface;
  use Webware\MessageBus\MessageInterface;
  use Webware\MessageBus\MiddlewareInterface;
+ use Webware\MessageBus\PipelineHandlerInterface;
  use Webware\MessageBus\ResultInterface;

  #[Override]
  public function process(
      MessageInterface $message,
-     MessageHandlerInterface $handler,
+     PipelineHandlerInterface $next,
  ): ResultInterface {
      // ...
-     return $handler->handle($message);
+     return $next->handle($message);
  }
```

Nothing else changes in the middleware bodies — the `instanceof` checks (`CommandInterface`,
`QueryInterface`, `CommandResultInterface`, `QueryResultInterface`, `EventAwareInterface`) and the
`EventDispatcherInterface` wiring are all unaffected.

### 2. Bump the dependency

In `composer.json`:

```diff
- "webware/message-bus": "^1.0.0",
+ "webware/message-bus": "^2.0",
```

Then `composer update webware/message-bus` (updates `composer.lock` and refreshes `vendor/`).

### 3. Update the middleware unit tests

- `test/unit/Middleware/CommandPreHandleMiddlewareTest.php`
- `test/unit/Middleware/CommandPostHandleMiddlewareTest.php`
- `test/unit/Middleware/QueryPreHandleMiddlewareTest.php`
- `test/unit/Middleware/QueryPostHandleMiddlewareTest.php`

These construct the middleware and pass a handler double as the second `process()` argument. That double
must change from a `MessageHandlerInterface` stub to a `PipelineHandlerInterface` stub (a
`handle(MessageInterface): ResultInterface` callable-object). Same one-method shape, just the interface
name changes.

### 4. Verify the integration tests still work

- `test/integration/LaminasServiceManagerCommandPipelineTest.php`
- `test/integration/LaminasServiceManagerQueryPipelineOptInTest.php`

These merge `BusConfigProvider` + `EventConfigProvider` output into a `ServiceManager`. v2's
`ConfigProvider` now also emits `dependencies.invokables` (EmptyPipelineHandler, strategies) and the
`StrategyInterface => HandleStrategy` alias, and the `MessageHandlerMiddleware` factory injects the
resolver + strategy. Since the tests already spread `factories`, `aliases`, and `invokables` from the bus
provider, they should keep working without changes — verify after the bump.

### 5. ConfigProvider — no change expected

`src/ConfigProvider.php` references only `BusProvider::MIDDLEWARE_PIPELINE_KEY` (still present in v2) and
wires the middleware by class name. The `@import-type` / `ProviderConfig` phpdoc shape may need the
`invokables` key added if it asserts the full bus config shape, but functionally no change is required.

### 6. Documentation

- Add a `docs/v2/` migration note (or a CHANGELOG entry) describing the required `MiddlewareInterface`
  signature change for downstream middleware authors and the `@internal` result-interface change.

## Design notes (resolved)

The `@internal` annotations on message-bus v2 types are intentional "type it, don't implement it"
markers, not gaps:

1. **`PipelineHandlerInterface` is `@internal` by design.** It appears in the `@api`
   `MiddlewareInterface::process()` signature, so middleware authors *reference* it as a type-hint but
   are not meant to *implement* it — the pipeline itself supplies the implementation (`Next` /
   `EmptyPipelineHandler`). Same "type it, don't implement it" rationale as the result interfaces. Our
   middleware type-hinting `PipelineHandlerInterface` is correct and needs no workaround.
2. **`CommandResultInterface`/`QueryResultInterface` are `@internal` by design.** The interfaces are
   meant for *typing* only, not *implementation* — `CommandResult`/`QueryResult` are `final`, and the
   `@internal` annotation signals "consume this as a type-hint, don't implement it." Making the
   interfaces `@api` would invite downstream code to implement them directly and bypass the final-class
   normalization of handler return types. Our `CommandPostHandleEvent`/`QueryPostHandleEvent` exposing
   them via `getResult()` is therefore intentional and fine — no change needed.
3. **`MessageHandlerInterface` is now an empty marker.** Our package no longer references it (after the
   middleware migration), so this is informational only.
