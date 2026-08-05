<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Event\Query\QueryPreHandleEvent;
use Webware\MessageBus\Query\QueryInterface;

#[CoversClass(QueryPreHandleEvent::class)]
final class QueryPreHandleEventTest extends TestCase
{
    #[Test]
    public function getNameDefaultsToClassName(): void
    {
        $event = new QueryPreHandleEvent(new class implements QueryInterface {});

        static::assertSame(QueryPreHandleEvent::class, $event->getName());
    }

    #[Test]
    public function getQueryReturnsConstructedQuery(): void
    {
        $query = new class implements QueryInterface {};
        $event = new QueryPreHandleEvent($query);

        static::assertSame($query, $event->getQuery());
    }

    #[Test]
    public function getTargetReturnsQuery(): void
    {
        $query = new class implements QueryInterface {};
        $event = new QueryPreHandleEvent($query);

        static::assertSame($query, $event->getTarget());
    }
}
