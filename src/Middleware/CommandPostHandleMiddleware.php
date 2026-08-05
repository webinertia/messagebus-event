<?php

declare(strict_types=1);

/**
 * This file is part of the Webware MessageBus Event package.
 *
 * Copyright (c) 2026 Joey (aka Tyrsson) Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webware\MessageBus\Event\Middleware;

use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Command\CommandResultInterface;
use Webware\MessageBus\Event\Command\CommandPostHandleEvent;
use Webware\MessageBus\Event\EventAwareInterface;
use Webware\MessageBus\MessageHandlerInterface;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MiddlewareInterface;
use Webware\MessageBus\ResultInterface;

final readonly class CommandPostHandleMiddleware implements MiddlewareInterface
{
    public function __construct(
        public EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function process(
        MessageInterface $message,
        MessageHandlerInterface $handler,
    ): ResultInterface {
        if ($message instanceof EventAwareInterface) {
            $event = $message->getEvent();
            if (null !== $event) {
                $this->eventDispatcher->dispatch($event);
            }
        }

        if ($message instanceof CommandResultInterface) {
            $this->eventDispatcher->dispatch(new CommandPostHandleEvent($message));

            return $message;
        }

        return $handler->handle($message);
    }
}
