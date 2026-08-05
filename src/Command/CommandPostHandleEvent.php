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
use Webware\MessageBus\Command\CommandResultInterface;
use Webware\MessageBus\Event\Event;

final class CommandPostHandleEvent extends Event
{
    public function __construct(
        private readonly CommandResultInterface $result,
    ) {
        parent::__construct(target: $result);
    }

    public function getCommand(): CommandInterface
    {
        return $this->result->getCommand();
    }

    public function getResult(): CommandResultInterface
    {
        return $this->result;
    }
}
