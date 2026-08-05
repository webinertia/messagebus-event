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

namespace Webware\MessageBus\Event\Command;

use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Event\Event;

final class CommandPreHandleEvent extends Event
{
    public function __construct(
        private readonly CommandInterface $command,
    ) {
        parent::__construct(target: $command);
    }

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
