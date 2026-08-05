<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event;

/** @api */
interface EventAwareInterface
{
    public function getEvent(): ?EventInterface;

    public function setEvent(EventInterface $event): void;
}
