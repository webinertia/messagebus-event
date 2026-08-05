# Getting Started

## Requirements

- PHP `~8.4.1 || ~8.5.0`
- [`webware/message-bus`](https://github.com/webinertia/message-bus) `^0.1.0` — **not** installed
  automatically by this package (it's a `require-dev` dependency here, used only by the test suite). Your
  application must require it directly.
- [`laminas/laminas-servicemanager`](https://github.com/laminas/laminas-servicemanager) `^4.0.0`
- [`phly/phly-event-dispatcher`](https://github.com/phly/phly-event-dispatcher) `^1.5.0`

## Installation

```bash
composer require webware/messagebus-event webware/message-bus
```

## Wiring

This package ships a `Webware\MessageBus\Event\ConfigProvider` that registers itself automatically if you
use [`laminas/laminas-config-aggregator`](https://github.com/laminas/laminas-config-aggregator) (see the
`extra.laminas.config-provider` entry in `composer.json`). Merge it with `webware/message-bus`'s own
`ConfigProvider` when building your application config, then build a `Laminas\ServiceManager\ServiceManager`
from the merged config:

```php
use Laminas\ServiceManager\ServiceManager;

$config = [];
foreach (
    [
        Webware\MessageBus\ConfigProvider::class,
        Webware\MessageBus\Event\ConfigProvider::class,
        // ...your own config providers
    ] as $provider
) {
    $config = array_merge_recursive($config, (new $provider())());
}

$container = new ServiceManager($config['dependencies']);
$container->setService('config', $config);
```

Once wired, resolving `Webware\MessageBus\MessageBusInterface` from the container gives you a bus whose
default middleware pipeline dispatches `CommandPreHandleEvent` before a command handler runs, and
`CommandPostHandleEvent` after it completes. Query events are **not** wired by default — see
[Usage Examples](usage-examples.md#opting-in-to-query-events) to enable them.

## Registering Listeners

Listeners are configured under the `listeners` and `listener_providers` keys, read by
`Webware\MessageBus\Event\Container\ListenerProviderAggregateFactory`:

```php
return [
    'listeners' => [
        Webware\MessageBus\Event\Command\CommandPostHandleEvent::class => [
            // a service id resolved from the container, or a plain callable/class-string
            MyListener::class,
            // a shape entry supporting an explicit priority (higher runs first)
            ['listener' => MyOtherListener::class, 'priority' => 10],
        ],
    ],
    'listener_providers' => [
        // service ids of PSR-14 ListenerProviderInterface implementations
        MyCustomListenerProvider::class,
    ],
];
```

See the [API Reference](api-reference.md#configprovider) for the full config shape.
