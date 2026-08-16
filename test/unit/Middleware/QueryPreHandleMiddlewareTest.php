<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;
use Webware\MessageBus\Event\Query\QueryPreHandleEvent;
use Webware\MessageBus\PipelineHandlerInterface;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\ResultInterface;

#[CoversClass(QueryPreHandleMiddleware::class)]
final class QueryPreHandleMiddlewareTest extends TestCase
{
    #[Test]
    public function processDispatchesQueryPreHandleEventWhenMessageIsQuery(): void
    {
        $query  = new class implements QueryInterface {};
        $result = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(static::callback(
                static fn(object $event): bool => (
                    $event instanceof QueryPreHandleEvent
                    && $query === $event->getQuery()
                ),
            ));

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($query)
            ->willReturn($result);

        $middleware = new QueryPreHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($query, $handler));
    }

    #[Test]
    public function processSkipsDispatchWhenMessageIsNotQuery(): void
    {
        $message = new class implements CommandInterface {};
        $result  = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message)
            ->willReturn($result);

        $middleware = new QueryPreHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($message, $handler));
    }
}
