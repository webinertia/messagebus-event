<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Webware\MessageBus\Event\Event;

#[CoversClass(Event::class)]
final class EventTest extends TestCase
{
    #[Test]
    public function constructorSetsTarget(): void
    {
        $target = new stdClass();
        $event  = new Event(target: $target);

        static::assertSame($target, $event->getTarget());
    }

    #[Test]
    public function getNameReturnsClassNameWhenNoNameProvided(): void
    {
        $event = new Event();

        static::assertSame(Event::class, $event->getName());
    }

    #[Test]
    public function getNameReturnsProvidedName(): void
    {
        $event = new Event(name: 'custom.event');

        static::assertSame('custom.event', $event->getName());
    }

    #[Test]
    public function getParamReturnsDefaultWhenMissing(): void
    {
        $event = new Event();

        static::assertSame('fallback', $event->getParam('missing', 'fallback'));
    }

    #[Test]
    public function getParamReturnsStoredValue(): void
    {
        $event = new Event(params: ['key' => 'value']);

        static::assertSame('value', $event->getParam('key'));
    }

    #[Test]
    public function getParamsReturnsAllParams(): void
    {
        $params = ['a' => 1, 'b' => 2];
        $event  = new Event(params: $params);

        static::assertSame($params, $event->getParams());
    }

    #[Test]
    public function getTargetReturnsNullByDefault(): void
    {
        $event = new Event();

        static::assertNull($event->getTarget());
    }

    #[Test]
    public function setNameUpdatesName(): void
    {
        $event = new Event();
        $event->setName('updated.event');

        static::assertSame('updated.event', $event->getName());
    }

    #[Test]
    public function setParamsReplacesAllParams(): void
    {
        $event = new Event(params: ['old' => 'value']);
        $event->setParams(['new' => 'value']);

        static::assertSame(['new' => 'value'], $event->getParams());
    }

    #[Test]
    public function setParamUpdatesSingleParam(): void
    {
        $event = new Event();
        $event->setParam('key', 'value');

        static::assertSame('value', $event->getParam('key'));
    }

    #[Test]
    public function setTargetUpdatesTarget(): void
    {
        $event  = new Event();
        $target = new stdClass();
        $event->setTarget($target);

        static::assertSame($target, $event->getTarget());
    }
}
