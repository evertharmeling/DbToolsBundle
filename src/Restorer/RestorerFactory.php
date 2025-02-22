<?php

declare(strict_types=1);

namespace MakinaCorpus\DbToolsBundle\Restorer;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use MakinaCorpus\DbToolsBundle\Error\NotImplementedException;
use MakinaCorpus\QueryBuilder\Bridge\Doctrine\DoctrineQueryBuilder;
use MakinaCorpus\QueryBuilder\Platform;
use Psr\Log\LoggerInterface;

class RestorerFactory
{
    /**
     * Constructor.
     *
     * @param array<string, string> $restorerBinaries
     * @param array<string, string> $restorerOptions
     */
    public function __construct(
        private ManagerRegistry $doctrineRegistry,
        private array $restorerBinaries,
        private array $restorerOptions = [],
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * Get a Restorer for given connection
     *
     * @throws \InvalidArgumentException
     */
    public function create(?string $connectionName = null): AbstractRestorer
    {
        /** @var Connection */
        $connection = $this->doctrineRegistry->getConnection($connectionName);
        $queryBuilder = new DoctrineQueryBuilder($connection);
        $platform = $queryBuilder->getServerFlavor();

        $restorer = match ($platform) {
            Platform::MARIADB => MariadbRestorer::class,
            Platform::MYSQL => MysqlRestorer::class,
            Platform::POSTGRESQL => PgsqlRestorer::class,
            Platform::SQLITE => SqliteRestorer::class,
            default => throw new NotImplementedException(\sprintf(
                "Restore is not implemented or configured for platform '%s' while using connection '%s'",
                $platform,
                $connectionName
            )),
        };

        $restorer = new $restorer(
            $this->restorerBinaries[$platform],
            $connection,
            $this->restorerOptions[$connectionName] ?? null
        );

        \assert($restorer instanceof AbstractRestorer);

        if ($this->logger) {
            $restorer->addLogger($this->logger);
        }

        return $restorer;
    }
}
