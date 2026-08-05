<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event;

use Override;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Event\Event;
use Webware\MessageBus\Event\EventAwareInterface;
use Webware\MessageBus\Event\EventAwareTrait;

#[CoversTrait(EventAwareTrait::class)]
final class EventAwareTraitTest extends TestCase
{
    private EventAwareInterface $subject;

    #[Test]
    public function getEventReturnsNullByDefault(): void
    {
        static::assertNull($this->subject->getEvent());
    }

    #[Test]
    public function setEventStoresEventForRetrieval(): void
    {
        $event = new Event();
        $this->subject->setEvent($event);

        static::assertSame($event, $this->subject->getEvent());
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class implements EventAwareInterface {
            use EventAwareTrait;
        };
    }
}
