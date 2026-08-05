<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event;

/** @api */
trait EventAwareTrait
{
    private ?EventInterface $event = null;

    public function getEvent(): ?EventInterface
    {
        return $this->event;
    }

    public function setEvent(EventInterface $event): void
    {
        $this->event = $event;
    }
}
