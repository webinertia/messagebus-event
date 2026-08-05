<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\Event\Command\CommandPostHandleEvent;
use Webware\MessageBus\MessageStatus;

#[CoversClass(CommandPostHandleEvent::class)]
final class CommandPostHandleEventTest extends TestCase
{
    #[Test]
    public function getCommandDelegatesToResult(): void
    {
        $command = new class implements CommandInterface {};
        $result  = new CommandResult($command, MessageStatus::Success, 'value');
        $event   = new CommandPostHandleEvent($result);

        static::assertSame($command, $event->getCommand());
    }

    #[Test]
    public function getResultReturnsConstructedResult(): void
    {
        $command = new class implements CommandInterface {};
        $result  = new CommandResult($command, MessageStatus::Success, 'value');
        $event   = new CommandPostHandleEvent($result);

        static::assertSame($result, $event->getResult());
    }

    #[Test]
    public function getTargetReturnsResult(): void
    {
        $command = new class implements CommandInterface {};
        $result  = new CommandResult($command, MessageStatus::Success, 'value');
        $event   = new CommandPostHandleEvent($result);

        static::assertSame($result, $event->getTarget());
    }
}
