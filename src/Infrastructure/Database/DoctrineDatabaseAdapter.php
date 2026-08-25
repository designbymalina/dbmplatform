<?php

/**
 * DBM Platform
 * Lightweight CMS ecosystem built on the DBM Framework.
 *
 * This software is proprietary and licensed.
 * Use of this software is subject to the terms of the DbM Platform License.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina
 * @license Proprietary
 *
 * @see /LICENSE_DBM_PLATFORM.txt
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Dbm\Database\Builder\CrudQueryBuilder;
use Dbm\Database\Contracts\CrudQueryBuilderInterface;
use Dbm\Database\Exceptions\QueryException;
use Dbm\Database\Hydrator\RowHydrator;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\ResultInterface;
use Dbm\Database\Contracts\SelectQueryBuilderInterface;
use Dbm\Database\Hydrator\SmartHydrator;
use Dbm\Infrastructure\Database\DoctrineResultAdapter;
use Dbm\Infrastructure\Log\Logger;
use Psr\Log\LoggerInterface;

class DoctrineDatabaseAdapter implements DatabaseInterface
{
    /** @var \Doctrine\DBAL\Connection */
    private object $conn;

    private CrudQueryBuilderInterface $builder;
    private RowHydrator $hydrator;
    private LoggerInterface $logger;

    public function __construct(object $connection, ?RowHydrator $hydrator = null)
    {
        $this->conn = $connection;
        $this->builder = new CrudQueryBuilder();

        $this->hydrator = $hydrator ?? new RowHydrator(
            new SmartHydrator()
        );

        $this->logger = new Logger();
    }

    public function databaseExists(string $database): bool
    {
        $platform = get_class($this->conn->getDatabasePlatform());

        return match (true) {
            str_contains($platform, 'MySQL') => (bool) $this->conn
                ->executeQuery(
                    'SHOW DATABASES LIKE ?',
                    [$database]
                )
                ->fetchOne(),

            str_contains($platform, 'PostgreSQL') => (bool) $this->conn
                ->executeQuery(
                    'SELECT 1 FROM pg_database WHERE datname = ?',
                    [$database]
                )
                ->fetchOne(),

            default => throw new \RuntimeException(
                "databaseExists not supported for platform {$platform}"
            ),
        };
    }

    public function selectDatabase(string $database): void
    {
        $this->conn->executeStatement("USE `$database`");
    }

    /** @inheritDoc */
    public function builder(): CrudQueryBuilderInterface
    {
        return $this->builder;
    }

    /** @inheritDoc */
    public function createQueryBuilder(): SelectQueryBuilderInterface
    {
        return new DoctrineQueryBuilderAdapter(
            $this->conn->createQueryBuilder()
        );
    }

    /** @inheritDoc */
    public function query(string $sql, array $params = [], array $types = []): ResultInterface
    {
        try {
            $result = $this->conn->executeQuery($sql, $params, $types);
            return new DoctrineResultAdapter($result);
        } catch (\Throwable $exception) {
            $this->wrapQueryException($exception, $sql, $params);
        }
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $params = [], array $types = []): ?array
    {
        $result = $this->query($sql, $params, $types);
        $row = $result->fetch();

        return $row ?: null;
    }

    /** @inheritDoc */
    public function fetchAll(string $sql, array $params = [], array $types = []): array
    {
        return $this->query($sql, $params, $types)->fetchAll() ?: [];
    }

    /** @inheritDoc */
    public function execute(string $sql, array $params = [], array $types = []): bool
    {
        try {
            $this->conn->executeStatement($sql, $params, $types);
            return true;
        } catch (\Throwable $exception) {
            $this->wrapQueryException($exception, $sql, $params);
        }
    }

    /** @inheritDoc */
    public function hydrate(?array $row, ?string $class = null): ?object
    {
        return $this->hydrator->hydrate($row, $class);
    }

    public function hydrateAll(array $rows, ?string $class = null): array
    {
        return $this->hydrator->hydrateAll($rows, $class);
    }

    /** @inheritDoc */
    public function getLastInsertId(): string
    {
        return $this->conn->lastInsertId();
    }

    /** @inheritDoc */
    public function beginTransaction(): void
    {
        $this->conn->beginTransaction();
    }

    /** @inheritDoc */
    public function inTransaction(): bool
    {
        return $this->conn->isTransactionActive();
    }

    /** @inheritDoc */
    public function commit(): void
    {
        $this->conn->commit();
    }

    /** @inheritDoc */
    public function rollback(): void
    {
        $this->conn->rollBack();
    }

    /** @inheritDoc */
    public function close(): void
    {
        $this->conn->close();
    }

    /** @inheritDoc */
    public function importSqlFile(string $filePath): bool
    {
        $sql = file_get_contents($filePath);
        $this->conn->executeStatement($sql);
        return true;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function wrapQueryException(\Throwable $e, string $sql, array $params = []): never
    {
        $this->logger->critical('Doctrine query failed', [
            'sql' => $sql,
            'params' => $params,
            'exception' => $e,
        ]);

        throw new QueryException($sql, $params, $e);
    }
}
