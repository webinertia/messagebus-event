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
use Webware\MessageBus\Event\Query\QueryPreHandleEvent;
use Webware\MessageBus\MessageHandlerInterface;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MiddlewareInterface;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\ResultInterface;

final readonly class QueryPreHandleMiddleware implements MiddlewareInterface
{
    public function __construct(
        public EventDispatcherInterface $eventDispatcher,
    ) {}

    #[Override]
    public function process(
        MessageInterface $message,
        MessageHandlerInterface $handler,
    ): ResultInterface {
        if ($message instanceof QueryInterface) {
            $this->eventDispatcher->dispatch(new QueryPreHandleEvent($message));
        }

        return $handler->handle($message);
    }
}
