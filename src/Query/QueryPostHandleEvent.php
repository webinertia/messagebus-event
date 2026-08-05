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

namespace Webware\MessageBus\Event\Query;

use Webware\MessageBus\Event\Event;
use Webware\MessageBus\Query\QueryInterface;
use Webware\MessageBus\Query\QueryResultInterface;

final class QueryPostHandleEvent extends Event
{
    public function __construct(
        private readonly QueryResultInterface $result,
    ) {
        parent::__construct(target: $result);
    }

    public function getQuery(): QueryInterface
    {
        return $this->result->getQuery();
    }

    public function getResult(): QueryResultInterface
    {
        return $this->result;
    }
}
