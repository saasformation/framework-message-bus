<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use Exception;
use League\Tactician\Logger\Formatter\Formatter;
use Psr\Log\LoggerInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\CommandInterface;

class TacticianCommandBusLogFormatter implements Formatter
{
    public function logCommandReceived(LoggerInterface $logger, $command)
    {
        if(!$command instanceof CommandInterface) {
            throw new Exception('Command must be instance of CommandInterface');
        }

        $logger->debug("Command {$command->getCommandCode()} received");
    }

    public function logCommandSucceeded(LoggerInterface $logger, $command, $returnValue)
    {
        if(!$command instanceof CommandInterface) {
            throw new Exception('Command must be instance of CommandInterface');
        }

        $logger->debug("Command {$command->getCommandCode()} succeeded");
    }

    public function logCommandFailed(LoggerInterface $logger, $command, Exception $e)
    {
        if(!$command instanceof CommandInterface) {
            throw new Exception('Command must be instance of CommandInterface');
        }

        $logger->error("Command {$command->getCommandCode()} failed", [
            'error' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]
        ]);
    }
}