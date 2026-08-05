<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event;

use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Webware\MessageBus\Event\ConfigProvider;
use Webware\MessageBus\Event\Container\CommandPostHandleMiddlewareFactory;
use Webware\MessageBus\Event\Container\CommandPreHandleMiddlewareFactory;
use Webware\MessageBus\Event\Container\ListenerProviderAggregateFactory;
use Webware\MessageBus\Event\Container\QueryPostHandleMiddlewareFactory;
use Webware\MessageBus\Event\Container\QueryPreHandleMiddlewareFactory;
use Webware\MessageBus\Event\Middleware\CommandPostHandleMiddleware;
use Webware\MessageBus\Event\Middleware\CommandPreHandleMiddleware;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;
use Webware\MessageBus\MessageBusInterface;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    #[Test]
    public function invokeRegistersEmptyDefaultListenersAndProviders(): void
    {
        $config = (new ConfigProvider())();

        static::assertSame([], $config[ConfigProvider::LISTENER_KEY]);
        static::assertSame([], $config[ConfigProvider::LISTENER_PROVIDER_KEY]);
    }

    #[Test]
    public function invokeRegistersEventDispatcherAndListenerProviderAliases(): void
    {
        $config = (new ConfigProvider())();

        static::assertSame(
            [
                EventDispatcherInterface::class  => EventDispatcher::class,
                ListenerProviderInterface::class => ListenerProviderAggregate::class,
            ],
            $config['dependencies']['aliases'],
        );
    }

    #[Test]
    public function invokeRegistersMiddlewareFactories(): void
    {
        $config = (new ConfigProvider())();

        static::assertSame(
            [
                ListenerProviderAggregate::class   => ListenerProviderAggregateFactory::class,
                CommandPostHandleMiddleware::class => CommandPostHandleMiddlewareFactory::class,
                CommandPreHandleMiddleware::class  => CommandPreHandleMiddlewareFactory::class,
                QueryPostHandleMiddleware::class   => QueryPostHandleMiddlewareFactory::class,
                QueryPreHandleMiddleware::class    => QueryPreHandleMiddlewareFactory::class,
            ],
            $config['dependencies']['factories'],
        );
    }

    #[Test]
    public function invokeWiresOnlyCommandMiddlewareIntoDefaultPipeline(): void
    {
        $config = (new ConfigProvider())();

        static::assertSame(
            [
                ['middleware' => CommandPreHandleMiddleware::class, 'priority' => 100],
                ['middleware' => CommandPostHandleMiddleware::class, 'priority' => -100],
            ],
            $config[MessageBusInterface::class]['middleware_pipeline'],
        );
    }

    #[Test]
    public function listenerKeyConstantIsListeners(): void
    {
        static::assertSame('listeners', ConfigProvider::LISTENER_KEY);
    }

    #[Test]
    public function listenerProviderKeyConstantIsListenerProviders(): void
    {
        static::assertSame('listener_providers', ConfigProvider::LISTENER_PROVIDER_KEY);
    }
}
