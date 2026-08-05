<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event;

use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Event\EventPropagationTrait;
use WebwareTestAsset\MessageBus\Event\PropagationAwareFixture;

#[CoversClass(EventPropagationTrait::class)]
final class EventPropagationTraitTest extends TestCase
{
    private PropagationAwareFixture $subject;

    #[Test]
    public function isPropagationStoppedReturnsFalseByDefault(): void
    {
        static::assertFalse($this->subject->isPropagationStopped());
    }

    #[Test]
    public function stopPropagationAcceptsExplicitFlag(): void
    {
        $this->subject->stopPropagation();
        $this->subject->stopPropagation(false);

        static::assertFalse($this->subject->isPropagationStopped());
    }

    #[Test]
    public function stopPropagationStopsPropagationByDefault(): void
    {
        $this->subject->stopPropagation();

        static::assertTrue($this->subject->isPropagationStopped());
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PropagationAwareFixture();
    }
}
