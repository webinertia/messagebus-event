# Contributing

## Setup

```bash
git clone https://github.com/webinertia/messagebus-event.git
cd messagebus-event
composer install
```

## Running Tests

```bash
composer test              # unit tests only (test/unit)
composer test-integration   # integration tests only (test/integration)
composer test-all           # both suites
composer test-coverage      # unit tests with a Clover coverage report
```

Tests are organized by autoload-dev namespace:

| Namespace | Directory | Purpose |
|---|---|---|
| `WebwareTest\MessageBus\Event\` | `test/unit/` | Unit tests, one class per source file. |
| `WebwareIntegrationTest\MessageBus\Event\` | `test/integration/` | Real `Laminas\ServiceManager\ServiceManager` wiring both this package's and `webware/message-bus`'s `ConfigProvider`s. |
| `WebwareTestAsset\MessageBus\Event\` | `test/asset/` | Shared fixtures/test doubles (e.g. `TestCommand`, `RecordingListener`). |

`phpunit.xml.dist` requires coverage metadata on every test (`#[CoversClass]` or `#[CoversNothing]`) and
fails the run on any notice, deprecation, or warning.

## Static Analysis

This project uses [Mago](https://github.com/carthage-software/mago) for formatting, linting, and static
analysis, configured in `mago.toml` against both `src` and `test`:

```bash
mago format   # auto-format
mago lint     # style/best-practice rules (mago lint --fix auto-fixes some)
mago analyze  # static type analysis
```

All three must report no issues before submitting a pull request. If a lint rule is a genuine false
positive, suppress the specific line with a `// @mago-expect lint:<rule-name>` comment directly above it
rather than disabling the rule globally.

## Pull Requests

- Keep changes focused; unrelated formatting-only diffs make review harder.
- Add or update tests for any behavior change.
- Update the relevant page(s) under `docs/v1/` when public API or configuration shape changes.
