<?php

declare(strict_types=1);

namespace Webware\MessageBus\Event\Container;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;

/** @internal */
final readonly class QueryPreHandleMiddlewareFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): QueryPreHandleMiddleware
    {
        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        return new QueryPreHandleMiddleware($eventDispatcher);
    }
}
