<?php

declare(strict_types=1);

namespace WebwareTestAsset\MessageBus\Event;

use Webware\MessageBus\Event\EventPropagationInterface;
use Webware\MessageBus\Event\EventPropagationTrait;

final class PropagationAwareFixture implements EventPropagationInterface
{
    use EventPropagationTrait;
}
