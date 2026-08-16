<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\Command\CommandResultInterface;
use Webware\MessageBus\Event\Command\CommandPostHandleEvent;
use Webware\MessageBus\Event\EventAwareInterface;
use Webware\MessageBus\Event\EventInterface;
use Webware\MessageBus\Event\Middleware\CommandPostHandleMiddleware;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MessageStatus;
use Webware\MessageBus\PipelineHandlerInterface;
use Webware\MessageBus\ResultInterface;
use Webware\MessageBus\StatusInterface;

#[CoversClass(CommandPostHandleMiddleware::class)]
final class CommandPostHandleMiddlewareTest extends TestCase
{
    #[Test]
    public function processDispatchesBothEventsWhenMessageIsEventAwareCommandResult(): void
    {
        // @mago-expect lint:no-redundant-variable
        $command  = new class implements CommandInterface {};
        $ownEvent = $this->createStub(EventInterface::class);

        $result = new class($command, $ownEvent) implements CommandResultInterface, EventAwareInterface {
            public function __construct(
                private readonly CommandInterface $command,
                private readonly EventInterface $ownEvent,
            ) {}

            public function getCommand(): CommandInterface
            {
                return $this->command;
            }

            public function getEvent(): ?EventInterface
            {
                return $this->ownEvent;
            }

            public function getResult(): mixed
            {
                return 'value';
            }

            public function getStatus(): StatusInterface
            {
                return MessageStatus::Success;
            }

            public function setEvent(EventInterface $event): void {}
        };

        $dispatchedEvents = [];
        $eventDispatcher  = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(static function (object $event) use (&$dispatchedEvents): object {
                $dispatchedEvents[] = $event;

                return $event;
            });

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new CommandPostHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($result, $handler));
        static::assertSame($ownEvent, $dispatchedEvents[0]);
        static::assertInstanceOf(CommandPostHandleEvent::class, $dispatchedEvents[1]);
    }

    #[Test]
    public function processDispatchesCommandPostHandleEventAndReturnsResultDirectlyWhenCommandResult(): void
    {
        $command = new class implements CommandInterface {};
        $result  = new CommandResult($command, MessageStatus::Success, 'value');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(static::callback(
                static fn(object $event): bool => (
                    $event instanceof CommandPostHandleEvent
                    && $result === $event->getResult()
                ),
            ));

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $middleware = new CommandPostHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($result, $handler));
    }

    #[Test]
    public function processDispatchesEventAwareEventAndForwardsToHandlerWhenNotCommandResult(): void
    {
        $ownEvent = $this->createStub(EventInterface::class);
        $message  = new class($ownEvent) implements EventAwareInterface, MessageInterface {
            public function __construct(
                private readonly EventInterface $ownEvent,
            ) {}

            public function getEvent(): ?EventInterface
            {
                return $this->ownEvent;
            }

            public function setEvent(EventInterface $event): void {}
        };
        $result = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($ownEvent);

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message)
            ->willReturn($result);

        $middleware = new CommandPostHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($message, $handler));
    }

    #[Test]
    public function processDoesNotDispatchWhenEventAwareMessageHasNoEvent(): void
    {
        $message = new class implements EventAwareInterface, MessageInterface {
            public function getEvent(): ?EventInterface
            {
                return null;
            }

            public function setEvent(EventInterface $event): void {}
        };
        $result = $this->createStub(ResultInterface::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $handler = $this->createMock(PipelineHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($message)
            ->willReturn($result);

        $middleware = new CommandPostHandleMiddleware($eventDispatcher);

        static::assertSame($result, $middleware->process($message, $handler));
    }
}
