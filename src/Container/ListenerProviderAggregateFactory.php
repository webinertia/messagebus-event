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

namespace Webware\MessageBus\Event\Container;

use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use Psl\Type;
use Psl\Type\Exception\AssertException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Webware\MessageBus\Event\ConfigProvider;
use Webware\MessageBus\Event\ListenerInterface;

use function is_callable;
use function is_string;

/**
 * @import-type ListenerConfig from ConfigProvider
 * @import-type ListenerProviderConfig from ConfigProvider
 * @internal
 * */
final class ListenerProviderAggregateFactory
{
    /**
     * @throws AssertException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ListenerProviderAggregate
    {
        $config = Type\dict(Type\array_key(), Type\mixed())->assert($container->get('config'));
        /** @var ListenerConfig $listeners */
        $listeners = $config[ConfigProvider::LISTENER_KEY] ?? [];
        /** @var ListenerProviderConfig $listenerProviders */
        $listenerProviders   = $config[ConfigProvider::LISTENER_PROVIDER_KEY] ?? [];
        $prioritizedProvider = $container->get(PrioritizedListenerProvider::class);
        $attachableProvider  = $container->get(AttachableListenerProvider::class);
        $aggregate           = new ListenerProviderAggregate();

        foreach ($listeners as $eventType => $spec) {
            $specShape = Type\vec(Type\union(
                Type\string(),
                Type\shape([
                    'listener' => Type\union(Type\class_string(), Type\string(), Type\callable_type()),
                    'priority' => Type\optional(Type\int()),
                ]),
            ));
            $spec = $specShape->assert($spec);
            foreach ($spec as $listener) {
                if (is_string($listener)) {
                    if ($container->has($listener)) {
                        // @mago-expect analysis:mixed-argument
                        $attachableProvider->listen($eventType, $container->get($listener));

                        // @mago-expect lint:no-else-clause
                    } elseif (is_callable($listener)) {
                        $attachableProvider->listen($eventType, $listener);
                    }

                    continue;
                }

                if ([] !== $listener) {
                    $resolvedListener = null;
                    if (
                        ! is_callable($listener['listener'])
                            && $container->has($listener['listener'])
                    ) {
                        /** @var ListenerInterface $resolvedListener */
                        $resolvedListener = $container->get($listener['listener']);

                        // @mago-expect lint:no-else-clause
                    } elseif (is_callable($listener['listener'])) {
                        $resolvedListener = $listener['listener'];

                        // @mago-expect lint:no-else-clause
                    } else {
                        continue;
                    }

                    $resolvedListener = Type\union(
                        Type\callable_type(),
                        Type\instance_of(ListenerInterface::class),
                    )->assert($resolvedListener);

                    // @mago-expect lint:no-isset
                    if (isset($listener['priority'])) {
                        $prioritizedProvider->listen($eventType, $resolvedListener, $listener['priority']);

                        // @mago-expect lint:no-else-clause
                    } else {
                        $attachableProvider->listen($eventType, $resolvedListener);
                    }

                    continue;
                }
            }
        }

        foreach ($listenerProviders as $provider) {
            /** @var ListenerProviderInterface|null $providerInstance */
            $providerInstance = $container->has($provider) ? $container->get($provider) : null;
            if ($providerInstance instanceof ListenerProviderInterface) {
                $aggregate->attach($providerInstance);
            }
        }

        $aggregate->attach($prioritizedProvider);
        $aggregate->attach($attachableProvider);

        return $aggregate;
    }
}
