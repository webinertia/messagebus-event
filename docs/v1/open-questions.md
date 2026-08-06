# Open Questions

Design tradeoffs and unresolved questions worth revisiting.

## `CommandResult`/`QueryResult` are `final` — what does that cost us?

`webware/message-bus`'s `Command\CommandResult` and `Query\QueryResult` are `final readonly` value objects.
They can't be subclassed. Tradeoffs of that decision:

**Lost:**

- **No result-level event-awareness.** `CommandPostHandleMiddleware`/`QueryPostHandleMiddleware` check
  `$message instanceof EventAwareInterface` on the result to optionally dispatch an extra domain event
  alongside the built-in `*PostHandleEvent`. Since the result classes can't be subclassed and don't
  implement `EventAwareInterface` themselves, this hook can never fire for a normalized result — any
  additional domain event must be dispatched manually from within the handler instead (see
  [Usage Examples](usage-examples.md#dispatching-a-custom-event-alongside-the-result)).
- **No handler-specific typed accessors on the result.** The payload is just `mixed $result`. A subclass
  could otherwise expose something like `getUserId(): int` instead of forcing every consumer to
  unpack/cast the raw payload from `getResult()`.
- **No polymorphic result types.** Every result is structurally the same `CommandResult`/`QueryResult`,
  distinguished only by the shape of its `mixed` payload at runtime — not by type (`instanceof
  CreateUserResult` isn't possible).
- **No overriding behavior** — `getStatus()`/`getResult()`/`getCommand()` are fixed implementations.

**Gained:**

- Every result flowing through the bus is guaranteed to be the exact same normalized, immutable shape.
  Middleware, listeners, and any generic consumer never have to worry about a handler having subtly
  changed or extended the contract — consistency across the application is traded for per-handler
  expressiveness.

Composition (implementing `CommandResultInterface`/`QueryResultInterface` directly and delegating to an
internal `CommandResult`/`QueryResult`) can recover some of the lost flexibility, but at the cost of
writing a bespoke result class per handler — which defeats the normalization goal the `final` classes were
introduced for. Current guidance (see [Usage Examples](usage-examples.md)) is to always return the
package-provided `CommandResult`/`QueryResult` directly and not write custom result classes.
