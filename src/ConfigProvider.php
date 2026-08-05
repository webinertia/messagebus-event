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

namespace Webware\MessageBus\Event;

use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Webware\MessageBus\ConfigProvider as BusProvider;
use Webware\MessageBus\MessageBusInterface;

/**
 * @import-type MiddlewarePipeline from BusProvider
 * @type Dependencies = array{
 *     aliases: array<class-string, class-string>,
 *     factories: array<class-string, class-string>,
 * }
 * @type ListenerConfig = array<class-string, array<int, string|array{listener: callable|class-string, priority?: int}>>
 * @type ListenerProviderConfig = array<class-string>
 * @type ProviderConfig = array{
 *      'dependencies': array{
 *          'aliases': array<class-string, class-string>,
 *          'factories': array<class-string, class-string>
 *      },
 *      'listener_providers': array{},
 *      'listeners': array{},
 *      Webware\MessageBus\MessageBusInterface: array{
 *          'middleware_pipeline': array<array-key, array{'middleware': class-string, 'priority'?: int}>
 *      }
 * }
 * @api
 */
final readonly class ConfigProvider
{
    public const string LISTENER_KEY = 'listeners';

    public const string LISTENER_PROVIDER_KEY = 'listener_providers';

    /** @return Dependencies */
    private function getDependencies(): array
    {
        return [
            'aliases'   => [
                EventDispatcherInterface::class  => EventDispatcher::class,
                ListenerProviderInterface::class => ListenerProviderAggregate::class,
            ],
            'factories' => [
                ListenerProviderAggregate::class              => Container\ListenerProviderAggregateFactory::class,
                Middleware\CommandPostHandleMiddleware::class => Container\CommandPostHandleMiddlewareFactory::class,
                Middleware\CommandPreHandleMiddleware::class  => Container\CommandPreHandleMiddlewareFactory::class,
                Middleware\QueryPostHandleMiddleware::class   => Container\QueryPostHandleMiddlewareFactory::class,
                Middleware\QueryPreHandleMiddleware::class    => Container\QueryPreHandleMiddlewareFactory::class,
            ],
        ];
    }

    /**
     * Only the command-scoped middleware is wired into the pipeline by default.
     * Register Middleware\QueryPreHandleMiddleware / Middleware\QueryPostHandleMiddleware
     * in your own middleware_pipeline config to opt in to query events.
     *
     * @return MiddlewarePipeline
     */
    private function getPipeline(): array
    {
        return [
            [
                'middleware' => Middleware\CommandPreHandleMiddleware::class,
                'priority'   => 100,
            ],
            [
                'middleware' => Middleware\CommandPostHandleMiddleware::class,
                'priority'   => -100,
            ],
        ];
    }

    /**
     * @return ProviderConfig
     */
    public function __invoke(): array
    {
        return [
            'dependencies'              => $this->getDependencies(),
            MessageBusInterface::class  => [
                BusProvider::MIDDLEWARE_PIPELINE_KEY => $this->getPipeline(),
            ],
            self::LISTENER_KEY          => [],
            self::LISTENER_PROVIDER_KEY => [],
        ];
    }
}
