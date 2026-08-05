<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event\Container;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Event\Middleware\CommandPostHandleMiddleware;

/** @internal */
final readonly class CommandPostHandleMiddlewareFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): CommandPostHandleMiddleware
    {
        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        return new CommandPostHandleMiddleware($eventDispatcher);
    }
}
