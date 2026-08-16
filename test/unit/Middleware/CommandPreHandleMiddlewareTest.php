<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Event\Command\CommandPreHandleEvent;
use Webware\MessageBus\Event\Middleware\CommandPreHandleMiddleware;
use Webware\MessageBus\PipelineHandlerInterface;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\ResultInterface;

#[CoversClass(CommandPreHandleMiddleware::class)]
final class CommandPreHandleMiddlewareTest extends TestCase
{
    #[Test]
    public function processDispatchesCommandPreHandleEventWhenMessageIsCommand(): void
    {
        $command = new class implements CommandInterface {};
        $result  = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(static::callback(
                static fn(object $event): bool => (
                    $event instanceof CommandPreHandleEvent
                    && $command === $event->getCommand()
                ),
            ));

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($result);

        $middleware = new CommandPreHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($command, $handler));
    }

    #[Test]
    public function processSkipsDispatchWhenMessageIsNotCommand(): void
    {
        $message = new class implements QueryInterface {};
        $result  = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message)
            ->willReturn($result);

        $middleware = new CommandPreHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($message, $handler));
    }
}
