<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Event\ConfigProvider as EventConfigProvider;
use Webware\MessageBus\Event\ConfigProvider;
use Webware\MessageBus\Event\Container\CommandPostHandleMiddlewareFactory;
use Webware\MessageBus\Event\Container\CommandPreHandleMiddlewareFactory;
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

        static::assertSame([], $config[EventConfigProvider::LISTENER_KEY]);
        static::assertSame([], $config[EventConfigProvider::LISTENER_PROVIDER_KEY]);
    }

    #[Test]
    public function invokeRegistersMiddlewareFactories(): void
    {
        $config = (new ConfigProvider())();

        static::assertSame(
            [
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
}
