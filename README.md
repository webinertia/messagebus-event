# Webware MessageBus Event

[![PHP Version](https://img.shields.io/packagist/php-v/webware/messagebus-event)](https://packagist.org/packages/webware/messagebus-event)
[![Latest Version](https://img.shields.io/packagist/v/webware/messagebus-event)](https://packagist.org/packages/webware/messagebus-event)
[![License](https://img.shields.io/github/license/webinertia/messagebus-event)](LICENSE)
[![Continuous Integration](https://github.com/webinertia/messagebus-event/actions/workflows/required/webinertia/.github/.github/workflows/continuous-integration.yml/badge.svg)](https://github.com/webinertia/messagebus-event/actions/workflows/required/webinertia/.github/.github/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/webinertia/messagebus-event/graph/badge.svg)](https://codecov.io/gh/webinertia/messagebus-event)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fwebinertia%2Fmessagebus-event%2F2.0.x)](https://dashboard.stryker-mutator.io/reports/github.com/webinertia/messagebus-event/2.0.x)

Event integration for [`webware/message-bus`](https://github.com/webinertia/message-bus), built on top of
[`phly/phly-event-dispatcher`](https://github.com/phly/phly-event-dispatcher) (PSR-14). It adds pre/post
"handle" events for both Command and Query messages, dispatched via middleware in the bus pipeline. Query
events are opt-in; Command events are wired into the default pipeline.

## Installation

```bash
composer require webware/messagebus-event webware/message-bus
```

## Documentation

Full documentation is published at <https://docs.webinertia.net/messagebus-event/v2/>.

- [Getting Started](https://docs.webinertia.net/messagebus-event/v2/getting-started.html) - requirements, installation, and wiring a `ServiceManager`.
- [Usage Examples](https://docs.webinertia.net/messagebus-event/v2/usage-examples.html) - registering listeners, opting in to Query events, custom
  events, stopping propagation.
- [API Reference](https://docs.webinertia.net/messagebus-event/v2/api-reference.html) - every public class, interface, trait, and the `ConfigProvider`
  config shape.
- [Contributing](https://docs.webinertia.net/messagebus-event/v2/contributing.html) - local setup, running tests, and static analysis.

Documentation for the 1.x line is archived at <https://docs.webinertia.net/messagebus-event/v1/>.

## Development

```bash
composer install
composer test-all      # unit + integration tests
composer bench          # PHPBench benchmarks (bench/)
mago format && mago lint && mago analyze
```

See [Contributing](https://docs.webinertia.net/messagebus-event/v1/contributing.html) for details.

## License

[BSD-3-Clause](LICENSE)
