<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Event\Command\CommandPreHandleEvent;

#[CoversClass(CommandPreHandleEvent::class)]
final class CommandPreHandleEventTest extends TestCase
{
    #[Test]
    public function getCommandReturnsConstructedCommand(): void
    {
        $command = new class implements CommandInterface {};
        $event   = new CommandPreHandleEvent($command);

        static::assertSame($command, $event->getCommand());
    }

    #[Test]
    public function getNameDefaultsToClassName(): void
    {
        $event = new CommandPreHandleEvent(new class implements CommandInterface {});

        static::assertSame(CommandPreHandleEvent::class, $event->getName());
    }

    #[Test]
    public function getTargetReturnsCommand(): void
    {
        $command = new class implements CommandInterface {};
        $event   = new CommandPreHandleEvent($command);

        static::assertSame($command, $event->getTarget());
    }
}
