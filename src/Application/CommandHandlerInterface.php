<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Application;

use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;
use SaaSFormation\Framework\SharedKernel\Domain\DomainEventStream;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): DomainEventStream;
}