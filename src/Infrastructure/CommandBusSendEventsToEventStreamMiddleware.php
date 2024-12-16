<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use Assert\Assert;
use League\Tactician\Handler\CommandNameExtractor\CommandNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\MethodNameInflector\MethodNameInflector;
use League\Tactician\Middleware;
use SaaSFormation\Framework\SharedKernel\Application\EventDispatcher\EventDispatcherInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;
use SaaSFormation\Framework\SharedKernel\Common\Identity\IdInterface;
use SaaSFormation\Framework\SharedKernel\Domain\DomainEventStream;

readonly class CommandBusSendEventsToEventStreamMiddleware implements Middleware
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private CommandNameExtractor     $commandNameExtractor,
        private HandlerLocator           $handlerLocator,
        private MethodNameInflector      $methodNameInflector)
    {
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

        /** @var DomainEventStream $domainEventStream */
        $domainEventStream = $handler->{$methodName}($command);

        foreach($domainEventStream->events() as $event) {
            Assert::that($command->getRequestId())->isInstanceOf(IdInterface::class);
            Assert::that($command->getCorrelationId())->isInstanceOf(IdInterface::class);
            Assert::that($command->getCommandId())->isInstanceOf(IdInterface::class);
            $event->setRequestId($command->getRequestId());
            $event->setCorrelationId($command->getCorrelationId());
            $event->setGeneratorCommandId($command->getCommandId());
            $this->eventDispatcher->dispatch($event);
        }
    }
}