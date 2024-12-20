<?php declare(strict_types=1);

namespace SaaSFormation\Framework\MessageBus\Infrastructure;

use League\Tactician\CommandBus;
use SaaSFormation\Framework\MessageBus\Application\QueryBusInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryInterface;
use SaaSFormation\Framework\SharedKernel\Application\Messages\QueryResultInterface;

readonly class TacticianBasedQueryBus implements QueryBusInterface
{
    public function __construct(private CommandBus $queryBus)
    {
    }

    public function ask(QueryInterface $query): QueryResultInterface
    {
        $queryResult = $this->queryBus->handle($query);

        if(!$queryResult instanceof QueryResultInterface) {
            throw new \Exception("Query handlers must return an instance of QueryResultInterface");
        }

        return $queryResult;
    }
}