<?php

declare(strict_types=1);

namespace WebwareBench\MessageBus\Event;

use Laminas\ServiceManager\ServiceManager;
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\EventDispatcherFactory;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use PhpBench\Attributes as Bench;
use Webware\MessageBus\ConfigProvider as BusConfigProvider;
use Webware\MessageBus\Event\ConfigProvider as EventConfigProvider;
use Webware\MessageBus\Event\Middleware\QueryPostHandleMiddleware;
use Webware\MessageBus\Event\Middleware\QueryPreHandleMiddleware;
use Webware\MessageBus\MessageBusInterface;
use WebwareTestAsset\MessageBus\Event\TestCommand;
use WebwareTestAsset\MessageBus\Event\TestCommandHandler;
use WebwareTestAsset\MessageBus\Event\TestQuery;
use WebwareTestAsset\MessageBus\Event\TestQueryHandler;

/**
 * Compares dispatch cost with and without opting Query\QueryPreHandleMiddleware /
 * Query\QueryPostHandleMiddleware into the shared middleware pipeline.
 *
 * Because both Command and Query messages flow through the same pipeline, opting in
 * adds overhead to Command dispatch too (the Query middleware still runs its
 * `instanceof QueryInterface` check on every message) - hence benchmarking both
 * message types against both pipeline configurations.
 *
 * No listeners are registered, so these numbers are the floor cost of opting in
 * (middleware + event construction + a PSR-14 dispatch call with zero listeners).
 */
#[Bench\BeforeMethods('setUp')]
#[Bench\Revs(1000)]
#[Bench\Iterations(5)]
#[Bench\OutputTimeUnit('microseconds')]
final class QueryPipelineOptInBench
{
    private MessageBusInterface $withoutOptIn;

    private MessageBusInterface $withOptIn;

    #[Bench\Subject]
    public function benchCommandWithoutQueryOptIn(): void
    {
        $this->withoutOptIn->handle(new TestCommand());
    }

    #[Bench\Subject]
    public function benchCommandWithQueryOptIn(): void
    {
        $this->withOptIn->handle(new TestCommand());
    }

    #[Bench\Subject]
    public function benchQueryWithoutQueryOptIn(): void
    {
        $this->withoutOptIn->handle(new TestQuery());
    }

    #[Bench\Subject]
    public function benchQueryWithQueryOptIn(): void
    {
        $this->withOptIn->handle(new TestQuery());
    }

    public function setUp(): void
    {
        $this->withoutOptIn = $this->buildBusWithoutQueryOptIn();
        $this->withOptIn    = $this->buildBusWithQueryOptIn();
    }

    /**
     * @param list<array{middleware: class-string, priority: int}> $additionalPipeline
     */
    private function buildBus(array $additionalPipeline): MessageBusInterface
    {
        $busConfig   = (new BusConfigProvider())();
        $eventConfig = (new EventConfigProvider())();

        $pipeline = [
            ...$busConfig[MessageBusInterface::class][BusConfigProvider::MIDDLEWARE_PIPELINE_KEY],
            ...$eventConfig[MessageBusInterface::class][BusConfigProvider::MIDDLEWARE_PIPELINE_KEY],
            ...$additionalPipeline,
        ];

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
                TestCommandHandler::class          => TestCommandHandler::class,
                TestQueryHandler::class            => TestQueryHandler::class,
            ],
            'services'   => [
                'config' => [
                    MessageBusInterface::class                 => [
                        BusConfigProvider::COMMAND_MAP_KEY         => [TestCommand::class => TestCommandHandler::class],
                        BusConfigProvider::QUERY_MAP_KEY           => [TestQuery::class => TestQueryHandler::class],
                        BusConfigProvider::MIDDLEWARE_PIPELINE_KEY => $pipeline,
                    ],
                    EventConfigProvider::LISTENER_KEY          => [],
                    EventConfigProvider::LISTENER_PROVIDER_KEY => [],
                ],
            ],
        ]);

        return $container->get(MessageBusInterface::class);
    }

    private function buildBusWithoutQueryOptIn(): MessageBusInterface
    {
        return $this->buildBus([]);
    }

    private function buildBusWithQueryOptIn(): MessageBusInterface
    {
        return $this->buildBus([
            [
                'middleware' => QueryPreHandleMiddleware::class,
                'priority'   => 100,
            ],
            [
                'middleware' => QueryPostHandleMiddleware::class,
                'priority'   => -100,
            ],
        ]);
    }
}
