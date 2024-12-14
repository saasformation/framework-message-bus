<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use League\Tactician\CommandBus;
use SaaSFormation\Framework\MessageBus\Application\CommandBusInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;

readonly class TacticianBasedCommandBus implements CommandBusInterface
{
    public function __construct(private CommandBus $commandBus)
    {
    }

    public function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}