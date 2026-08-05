<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event;

/** @api */
trait EventPropagationTrait
{
    protected bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(bool $flag = true): void
    {
        $this->propagationStopped = $flag;
    }
}
