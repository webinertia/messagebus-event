<?php

declare(strict_types=1);

namespace WebwareTestAsset\MessageBus\Event;

use LogicException;
use Override;
use Webware\MessageBus\MessageHandlerInterface;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MessageStatus;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\Query\QueryResult;
use Webware\MessageBus\ResultInterface;

final class TestQueryHandler implements MessageHandlerInterface
{
    #[Override]
    public function handle(MessageInterface $message): ResultInterface
    {
        if (! $message instanceof QueryInterface) {
            throw new LogicException('TestQueryHandler can only handle QueryInterface messages.');
        }

        return new QueryResult($message, MessageStatus::Success, 'query-handled');
    }
}
