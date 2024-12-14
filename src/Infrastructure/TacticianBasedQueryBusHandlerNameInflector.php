<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use League\Tactician\Handler\MethodNameInflector\MethodNameInflector;

class TacticianBasedQueryBusHandlerNameInflector implements MethodNameInflector
{
    public function inflect($command, $commandHandler)
    {
        return 'ask';
    }
}