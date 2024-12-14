<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Application;

use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;

interface CommandBusInterface
{
    public function handle(CommandInterface $command): void;
}