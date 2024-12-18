<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use Assert\Assert;
use League\Tactician\Handler\CommandNameExtractor\CommandNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\MethodNameInflector\MethodNameInflector;
use League\Tactician\Middleware;
use SaaSFormation\Framework\MongoDBBasedReadModel\Infrastructure\ReadModel\MongoDBClient;
use SaaSFormation\Framework\MongoDBBasedReadModel\Infrastructure\ReadModel\MongoDBClientProvider;
use SaaSFormation\Framework\SharedKernel\Application\EventDispatcher\EventDispatcherInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;
use SaaSFormation\Framework\SharedKernel\Common\Identity\IdInterface;
use SaaSFormation\Framework\SharedKernel\Common\Identity\UUIDFactoryInterface;
use SaaSFormation\Framework\SharedKernel\Domain\DomainEventStream;
use SaaSFormation\Framework\SharedKernel\Domain\WriteModel\RepositoryInterface;

readonly class CommandBusSendEventsToEventStreamMiddleware implements Middleware
{
    private MongoDBClient $mongoDBClient;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private CommandNameExtractor     $commandNameExtractor,
        private HandlerLocator           $handlerLocator,
        private MethodNameInflector      $methodNameInflector,
        private RepositoryInterface      $repository,
        private UUIDFactoryInterface     $UUIDFactory,
        private MongoDBClientProvider    $mongoDBClientProvider
    )
    {
        $this->mongoDBClient = $this->mongoDBClientProvider->provide();
    }

    /**
     * @param CommandInterface $command
     * @param callable $next
     * @return void
     */
    public function execute($command, callable $next)
    {
        $commandName = $this->commandNameExtractor->extract($command);
        $handler = $this->handlerLocator->getHandlerForCommand($commandName);
        $methodName = $this->methodNameInflector->inflect($command, $handler);

        try {
            /** @var DomainEventStream $domainEventStream */
            $domainEventStream = $handler->{$methodName}($command);

            if (!$command->getCommandId()) {
                $command->setCommandId($this->UUIDFactory->generate());
            }

            foreach ($domainEventStream->events() as $event) {
                if (!$event->getDomainEventId()) {
                    $event->setDomainEventId($this->UUIDFactory->generate());
                }

                Assert::that($command->getRequestId())->isInstanceOf(IdInterface::class, "Request id is null at CommandBusSendEventsToEventStreamMiddleware");
                Assert::that($command->getCorrelationId())->isInstanceOf(IdInterface::class, "Correlation id is null at CommandBusSendEventsToEventStreamMiddleware");
                Assert::that($command->getCommandId())->isInstanceOf(IdInterface::class, "Command id is null at CommandBusSendEventsToEventStreamMiddleware");

                $event->setRequestId($command->getRequestId());
                $event->setCorrelationId($command->getCorrelationId());
                $event->setGeneratorCommandId($command->getCommandId());
                $this->repository->save($event);

                $this->mongoDBClient->beginTransaction($command->getRequestId());

                $this->eventDispatcher->dispatch($event);

                $this->mongoDBClient->commitTransaction($command->getRequestId());
            }

            $command->markAsSucceeded();
            $this->repository->saveCommand($command);
        } catch (\Throwable $e) {
            if($command->getRequestId() !== null) {
                $this->mongoDBClient->rollbackTransaction($command->getRequestId());
            }

            $command->markAsFailed();
            $this->repository->saveCommand($command);

            throw $e;
        }
    }
}