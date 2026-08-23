<?php

declare(strict_types=1);

namespace WebwareTestIntegration\MessageBus\Event;

use Laminas\ServiceManager\ServiceManager;
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\EventDispatcherFactory;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\ConfigProvider as BusConfigProvider;
use Webware\MessageBus\Event\ConfigProvider as EventConfigProvider;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;
use Webware\MessageBus\Event\Query\QueryPostHandleEvent;
use Webware\MessageBus\Event\Query\QueryPreHandleEvent;
use Webware\MessageBus\MessageBusInterface;
use Webware\MessageBus\Query\QueryResultInterface;
use WebwareTestAsset\MessageBus\Event\RecordingListener;
use WebwareTestAsset\MessageBus\Event\TestQuery;
use WebwareTestAsset\MessageBus\Event\TestQueryHandler;

/**
 * Query pre/post-handle middleware is opt-in: it is not part of
 * EventConfigProvider's default pipeline, so consuming applications must
 * register it in their own middleware_pipeline config to receive query events.
 */
#[CoversNothing]
final class LaminasServiceManagerQueryPipelineOptInTest extends TestCase
{
    #[Test]
    public function manuallyWiredQueryMiddlewareDispatchesQueryPreAndPostHandleEvents(): void
    {
        $busConfig   = (new BusConfigProvider())();
        $eventConfig = (new EventConfigProvider())();

        $container = new ServiceManager([
            'factories'  => [
                ...$busConfig['dependencies']['factories'],
                ...$eventConfig['dependencies']['factories'],
                EventDispatcher::class => EventDispatcherFactory::class,
            ],
            'aliases'    => [
                ...$busConfig['dependencies']['aliases'],
                ...$eventConfig['dependencies']['aliases'],
            ],
            'invokables' => [
                ...$busConfig['dependencies']['invokables'],
                PrioritizedListenerProvider::class => PrioritizedListenerProvider::class,
                AttachableListenerProvider::class  => AttachableListenerProvider::class,
                RecordingListener::class           => RecordingListener::class,
                TestQueryHandler::class            => TestQueryHandler::class,
            ],
            'services'   => [
                'config' => [
                    MessageBusInterface::class                 => [
                        BusConfigProvider::COMMAND_MAP_KEY         => [],
                        BusConfigProvider::QUERY_MAP_KEY           => [TestQuery::class => TestQueryHandler::class],
                        BusConfigProvider::MIDDLEWARE_PIPELINE_KEY => [
                            ...$busConfig[MessageBusInterface::class]['middleware_pipeline'],
                            ['middleware' => QueryPreHandleMiddleware::class, 'priority' => 100],
                            ['middleware' => QueryPostHandleMiddleware::class, 'priority' => -100],
                        ],
                    ],
                    EventConfigProvider::LISTENER_KEY          => [
                        QueryPreHandleEvent::class  => [
                            ['listener' => RecordingListener::class, 'priority' => 10],
                        ],
                        QueryPostHandleEvent::class => [
                            ['listener' => RecordingListener::class],
                        ],
                    ],
                    EventConfigProvider::LISTENER_PROVIDER_KEY => [],
                ],
            ],
        ]);

        $bus    = $container->get(MessageBusInterface::class);
        $query  = new TestQuery();
        $result = $bus->handle($query);

        static::assertInstanceOf(QueryResultInterface::class, $result);
        static::assertSame($query, $result->getQuery());
        static::assertSame('query-handled', $result->getResult());

        $listener = $container->get(RecordingListener::class);
        $events   = $listener->getEvents();

        static::assertCount(2, $events);
        static::assertInstanceOf(QueryPreHandleEvent::class, $events[0]);
        static::assertSame($query, $events[0]->getQuery());
        static::assertInstanceOf(QueryPostHandleEvent::class, $events[1]);
        static::assertSame($result, $events[1]->getResult());
        static::assertSame($query, $events[1]->getQuery());
    }
}
