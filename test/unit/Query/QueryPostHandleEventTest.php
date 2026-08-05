<?php

declare(strict_types=1);

namespace WebwareTest\MessageBus\Event\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Event\Query\QueryPostHandleEvent;
use Webware\MessageBus\MessageStatus;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\Query\QueryResult;

#[CoversClass(QueryPostHandleEvent::class)]
final class QueryPostHandleEventTest extends TestCase
{
    #[Test]
    public function getQueryDelegatesToResult(): void
    {
        $query  = new class implements QueryInterface {};
        $result = new QueryResult($query, MessageStatus::Success, 'value');
        $event  = new QueryPostHandleEvent($result);

        static::assertSame($query, $event->getQuery());
    }

    #[Test]
    public function getResultReturnsConstructedResult(): void
    {
        $query  = new class implements QueryInterface {};
        $result = new QueryResult($query, MessageStatus::Success, 'value');
        $event  = new QueryPostHandleEvent($result);

        static::assertSame($result, $event->getResult());
    }

    #[Test]
    public function getTargetReturnsResult(): void
    {
        $query  = new class implements QueryInterface {};
        $result = new QueryResult($query, MessageStatus::Success, 'value');
        $event  = new QueryPostHandleEvent($result);

        static::assertSame($result, $event->getTarget());
    }
}
