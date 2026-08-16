# Webware MessageBus Event

[![Continuous Integration](https://github.com/webinertia/messagebus-event/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/webinertia/messagebus-event/actions/workflows/continuous-integration.yml)
[![Coverage Status](https://coveralls.io/repos/github/webinertia/messagebus-event/badge.svg)](https://coveralls.io/github/webinertia/messagebus-event)

Event integration for [`webware/message-bus`](https://github.com/webinertia/message-bus), built on top of
[`phly/phly-event-dispatcher`](https://github.com/phly/phly-event-dispatcher) (PSR-14). It adds pre/post
"handle" events for both Command and Query messages, dispatched via middleware in the bus pipeline. Query
events are opt-in; Command events are wired into the default pipeline.

## Installation

```bash
composer require webware/messagebus-event webware/message-bus
```

## Documentation

Full documentation lives under [`docs/v2/`](docs/v2/README.md):

- [Getting Started](docs/v2/getting-started.md) — requirements, installation, and wiring a `ServiceManager`.
- [Usage Examples](docs/v2/usage-examples.md) — registering listeners, opting in to Query events, custom
  events, stopping propagation.
- [API Reference](docs/v2/api-reference.md) — every public class, interface, trait, and the `ConfigProvider`
  config shape.
- [Contributing](docs/v2/contributing.md) — local setup, running tests, and static analysis.

## Development

```bash
composer install
composer test-all      # unit + integration tests
composer bench          # PHPBench benchmarks (bench/)
mago format && mago lint && mago analyze
```

See [Contributing](docs/v1/contributing.md) for details.

## License

[BSD-3-Clause](LICENSE)
