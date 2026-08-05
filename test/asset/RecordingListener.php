<?php

declare(strict_types=1);

namespace WebwareTestAsset\MessageBus\Event;

use Override;
use Webware\MessageBus\Event\EventInterface;
use Webware\MessageBus\Event\ListenerInterface;

final class RecordingListener implements ListenerInterface
{
    /** @var list<EventInterface> */
    private array $events = [];

    /** @return list<EventInterface> */
    public function getEvents(): array
    {
        return $this->events;
    }

    #[Override]
    public function __invoke(EventInterface $event): void
    {
        $this->events[] = $event;
    }
}
