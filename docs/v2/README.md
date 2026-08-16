# Webware MessageBus Event

Event integration for [`webware/message-bus`](https://github.com/webinertia/message-bus), built on top of
[`phly/phly-event-dispatcher`](https://github.com/phly/phly-event-dispatcher) (PSR-14). It adds pre/post
"handle" events for both Command and Query messages, dispatched via middleware in the bus pipeline.

This documentation targets the v2 release line, aligned with `webware/message-bus` v2.

## Documentation

- [Getting Started](getting-started.md) — installation and wiring.
- [Usage Examples](usage-examples.md) — listening for events, opting in to query events, custom events.
- [API Reference](api-reference.md) — every public class, interface, and trait.
- [Contributing](contributing.md) — local setup, tests, and static analysis.
