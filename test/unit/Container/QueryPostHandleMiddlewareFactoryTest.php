<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Container;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Event\Container\QueryPostHandleMiddlewareFactory;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;

#[CoversClass(QueryPostHandleMiddlewareFactory::class)]
final class QueryPostHandleMiddlewareFactoryTest extends TestCase
{
    #[Test]
    public function invokeBuildsMiddlewareWithResolvedEventDispatcher(): void
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(EventDispatcherInterface::class)
            ->willReturn($eventDispatcher);

        $factory    = new QueryPostHandleMiddlewareFactory();
        $middleware = $factory($container);

        static::assertInstanceOf(QueryPostHandleMiddleware::class, $middleware);
        static::assertSame($eventDispatcher, $middleware->eventDispatcher);
    }
}
