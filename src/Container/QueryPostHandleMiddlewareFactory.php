<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event\Container;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;

/** @internal */
final readonly class QueryPostHandleMiddlewareFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): QueryPostHandleMiddleware
    {
        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        return new QueryPostHandleMiddleware($eventDispatcher);
    }
}
