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
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Event\Command\CommandPreHandleEvent;
use Webware\MessageBus\MessageInterface;
use Webware\MessageBus\MiddlewareInterface;
use Webware\MessageBus\PipelineHandlerInterface;
use Webware\MessageBus\ResultInterface;

final class CommandPreHandleMiddleware implements MiddlewareInterface
{
    public function __construct(
        public private(set) EventDispatcherInterface $eventDispatcher {
            get => $this->eventDispatcher;
            set(EventDispatcherInterface $value) => $this->eventDispatcher = $value;
        },
    ) {}

    #[Override]
    public function process(
        MessageInterface $message,
        PipelineHandlerInterface $next,
    ): ResultInterface {
        if ($message instanceof CommandInterface) {
            $this->eventDispatcher->dispatch(new CommandPreHandleEvent($message));
        }

        return $next->handle($message);
    }
}
