<?php

namespace SaaSFormation\Framework\MessageBus\Application;

use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryResultInterface;

interface QueryHandlerInterface
{
    public function ask(QueryInterface $query): QueryResultInterface;
}