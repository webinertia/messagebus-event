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

use Webware\Event\ConfigProvider as EventProvider;
use Webware\MessageBus\ConfigProvider as BusProvider;
use Webware\MessageBus\MessageBusInterface;

/**
 * The dispatcher, the listener provider aggregate and the config keys belong to
 * the centre: this package consumes `Webware\Event\ConfigProvider` for all three
 * and contributes only its own middleware wiring.
 *
 * @import-type ConfigShape from EventProvider
 * @import-type MiddlewarePipeline from BusProvider
 * @type Dependencies = array{factories: array<class-string, class-string>}
 * @api
 */
final readonly class ConfigProvider
{
    /** @return Dependencies */
    private function getDependencies(): array
    {
        return [
            'factories' => [
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
     * @return ConfigShape
     */
    public function __invoke(): array
    {
        return [
            'dependencies'                       => $this->getDependencies(),
            MessageBusInterface::class           => [
                BusProvider::MIDDLEWARE_PIPELINE_KEY => $this->getPipeline(),
            ],
            EventProvider::LISTENER_KEY          => [],
            EventProvider::LISTENER_PROVIDER_KEY => [],
        ];
    }
}
