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

final class QueryPreHandleEvent extends Event
{
    public function __construct(
        private readonly QueryInterface $query,
    ) {
        parent::__construct(target: $query);
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }
}
