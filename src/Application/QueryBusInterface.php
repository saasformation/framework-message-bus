<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Application;

use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryResultInterface;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): QueryResultInterface;
}