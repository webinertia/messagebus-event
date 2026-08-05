<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event;

/** @api */
interface EventPropagationInterface
{
    public function isPropagationStopped(): bool;

    public function stopPropagation(): void;
}
