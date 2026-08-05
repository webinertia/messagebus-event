<?php

declare(strict_types=1);

namespace WebwareTestAsset\MessageBus\Event;

use LogicException;
use Override;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\MessageHandlerInterface;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MessageStatus;
use Webware\MessageBus\ResultInterface;

final class TestCommandHandler implements MessageHandlerInterface
{
    #[Override]
    public function handle(MessageInterface $message): ResultInterface
    {
        if (! $message instanceof CommandInterface) {
            throw new LogicException('TestCommandHandler can only handle CommandInterface messages.');
        }

        return new CommandResult($message, MessageStatus::Success, 'command-handled');
    }
}
