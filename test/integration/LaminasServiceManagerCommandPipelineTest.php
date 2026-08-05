<?php

declare(strict_types=1);

namespace WebwareIntegrationTest\MessageBus\Event;

use Laminas\ServiceManager\ServiceManager;
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\EventDispatcherFactory;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\MessageBus\Command\CommandResultInterface;
use Webware\MessageBus\ConfigProvider as BusConfigProvider;
use Webware\MessageBus\Event\Command\CommandPostHandleEvent;
use Webware\MessageBus\Event\Command\CommandPreHandleEvent;
use Webware\MessageBus\Event\ConfigProvider as EventConfigProvider;
use Webware\MessageBus\MessageBusInterface;
use WebwareTestAsset\MessageBus\Event\RecordingListener;
use WebwareTestAsset\MessageBus\Event\TestCommand;
use WebwareTestAsset\MessageBus\Event\TestCommandHandler;

#[CoversNothing]
final class LaminasServiceManagerCommandPipelineTest extends TestCase
{
    #[Test]
    public function defaultPipelineDispatchesCommandPreAndPostHandleEvents(): void
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
                TestCommandHandler::class          => TestCommandHandler::class,
            ],
            'services'   => [
                'config' => [
                    MessageBusInterface::class                 => [
                        BusConfigProvider::COMMAND_MAP_KEY         => [TestCommand::class => TestCommandHandler::class],
                        BusConfigProvider::QUERY_MAP_KEY           => [],
                        BusConfigProvider::MIDDLEWARE_PIPELINE_KEY => [
                            ...$busConfig[MessageBusInterface::class]['middleware_pipeline'],
                            ...$eventConfig[MessageBusInterface::class]['middleware_pipeline'],
                        ],
                    ],
                    EventConfigProvider::LISTENER_KEY          => [
                        CommandPreHandleEvent::class  => [
                            ['listener' => RecordingListener::class, 'priority' => 10],
                        ],
                        CommandPostHandleEvent::class => [
                            ['listener' => RecordingListener::class],
                        ],
                    ],
                    EventConfigProvider::LISTENER_PROVIDER_KEY => [],
                ],
            ],
        ]);

        $bus     = $container->get(MessageBusInterface::class);
        $command = new TestCommand();
        $result  = $bus->handle($command);

        static::assertInstanceOf(CommandResultInterface::class, $result);
        static::assertSame($command, $result->getCommand());
        static::assertSame('command-handled', $result->getResult());

        $listener = $container->get(RecordingListener::class);
        $events   = $listener->getEvents();

        static::assertCount(2, $events);
        static::assertInstanceOf(CommandPreHandleEvent::class, $events[0]);
        static::assertSame($command, $events[0]->getCommand());
        static::assertInstanceOf(CommandPostHandleEvent::class, $events[1]);
        static::assertSame($result, $events[1]->getResult());
        static::assertSame($command, $events[1]->getCommand());
    }
}
