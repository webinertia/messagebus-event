<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Container;

use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use stdClass;
use Webware\MessageBus\Event\ConfigProvider;
use Webware\MessageBus\Event\Container\ListenerProviderAggregateFactory;
use Webware\MessageBus\Event\EventInterface;
use Webware\MessageBus\Event\ListenerInterface;

use function in_array;
use function iterator_to_array;

#[CoversClass(ListenerProviderAggregateFactory::class)]
final class ListenerProviderAggregateFactoryTest extends TestCase
{
    #[Test]
    public function invokeAttachesCallableShapeListenerDirectly(): void
    {
        $listener = static function (EventInterface $event): void {};

        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => [
                        ['listener' => $listener],
                    ],
                ],
            ],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([$listener], $listeners);
    }

    #[Test]
    public function invokeAttachesCallableStringListenerWhenNotRegisteredInContainer(): void
    {
        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => ['strlen'],
                ],
            ],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame(['strlen'], $listeners);
    }

    #[Test]
    public function invokeAttachesRegisteredListenerProviders(): void
    {
        $customProvider = new class implements ListenerProviderInterface {
            public function getListenersForEvent(object $event): iterable
            {
                yield 'custom.listener';
            }
        };

        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_PROVIDER_KEY => [$customProvider::class],
            ],
            getMap: [$customProvider::class => $customProvider],
            availableIds: [$customProvider::class],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame(['custom.listener'], $listeners);
    }

    #[Test]
    public function invokeAttachesShapeListenersToPrioritizedProviderInPriorityOrder(): void
    {
        $low = new class implements ListenerInterface {
            public function __invoke(EventInterface $event): void {}
        };
        $high = new class implements ListenerInterface {
            public function __invoke(EventInterface $event): void {}
        };

        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => [
                        ['listener' => 'low.listener', 'priority' => 1],
                        ['listener' => 'high.listener', 'priority' => 10],
                    ],
                ],
            ],
            getMap: ['low.listener' => $low, 'high.listener' => $high],
            availableIds: ['low.listener', 'high.listener'],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([$high, $low], $listeners);
    }

    #[Test]
    public function invokeAttachesShapeListenerWithoutPriorityToAttachableProvider(): void
    {
        $listener = new class implements ListenerInterface {
            public function __invoke(EventInterface $event): void {}
        };

        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => [
                        ['listener' => 'my.listener'],
                    ],
                ],
            ],
            getMap: ['my.listener' => $listener],
            availableIds: ['my.listener'],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([$listener], $listeners);
    }

    #[Test]
    public function invokeAttachesStringListenerResolvedFromContainer(): void
    {
        $listener = new class implements ListenerInterface {
            public function __invoke(EventInterface $event): void {}
        };

        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => ['my.listener'],
                ],
            ],
            getMap: ['my.listener' => $listener],
            availableIds: ['my.listener'],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([$listener], $listeners);
    }

    #[Test]
    public function invokeSkipsListenerProviderNotRegisteredInContainer(): void
    {
        $container = $this->buildContainer(
            config: [
                // @mago-expect lint:no-literal-namespace-string
                ConfigProvider::LISTENER_PROVIDER_KEY => ['Unresolvable\\Provider'],
            ],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([], $listeners);
    }

    #[Test]
    public function invokeSkipsShapeListenerWhenNeitherCallableNorInContainer(): void
    {
        $container = $this->buildContainer(
            config: [
                ConfigProvider::LISTENER_KEY => [
                    stdClass::class => [
                        // @mago-expect lint:no-literal-namespace-string
                        ['listener' => 'Unresolvable\\Listener'],
                    ],
                ],
            ],
        );

        $aggregate = (new ListenerProviderAggregateFactory())($container);
        $listeners = iterator_to_array($aggregate->getListenersForEvent(new stdClass()), preserve_keys: false);

        static::assertSame([], $listeners);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $getMap
     * @param list<string> $availableIds
     */
    private function buildContainer(array $config, array $getMap = [], array $availableIds = []): ContainerInterface
    {
        $fullGetMap = [
            'config'                           => $config,
            PrioritizedListenerProvider::class => new PrioritizedListenerProvider(),
            AttachableListenerProvider::class  => new AttachableListenerProvider(),
            ...$getMap,
        ];

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnCallback(
                static fn(string $id): mixed => $fullGetMap[$id],
            );
        $container->method('has')
            ->willReturnCallback(
                static fn(string $id): bool => in_array($id, $availableIds, strict: true),
            );

        return $container;
    }
}
