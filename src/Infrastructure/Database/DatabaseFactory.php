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

use App\Infrastructure\Database\DoctrineDatabaseAdapter;
use Dbm\Core\Config\AppConfig;
use Dbm\Database\Adapter\PdoDatabaseAdapter;
use Dbm\Database\Contracts\ConnectionFactoryInterface;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Validator\DatabaseEnvValidator;
use PDO;

final class DatabaseFactory implements ConnectionFactoryInterface
{
    public function create(): DatabaseInterface
    {
        return self::createDatabase();
    }

    public static function createDatabase(): PdoDatabaseAdapter|DoctrineDatabaseAdapter
    {
        $driverRaw = getenv('DB_DRIVER') ?: 'PDO|pdo_mysql';

        $parts = explode('|', $driverRaw);

        if (count($parts) !== 2) {
            $main = 'PDO';
            $sub = 'pdo_mysql';
        } else {
            [$main, $sub] = $parts;
            $main = strtoupper(trim($main));
            $sub = strtolower(trim($sub));
        }

        return match ($main) {
            'DOCTRINE' => self::createDoctrineFromEnv($sub),
            'PDO' => self::createPdoFromEnv($sub),
            default => self::createPdoFromEnv('pdo_mysql'),
        };
    }

    private static function createPdoFromEnv(string $sub): PdoDatabaseAdapter
    {
        $pdoDriver = match ($sub) {
            'pdo_pgsql' => 'pgsql',
            'pdo_sqlite' => 'sqlite',
            default => 'mysql',
        };

        if (!in_array($pdoDriver, PDO::getAvailableDrivers(), true)) {
            throw new \RuntimeException(
                "PDO driver '{$pdoDriver}' is not available. Installed drivers: "
                . implode(', ', PDO::getAvailableDrivers())
            );
        }

        DatabaseEnvValidator::validate(requireDatabase: $pdoDriver !== 'sqlite');

        return new PdoDatabaseAdapter(
            dbHost: getenv('DB_HOST'),
            dbUser: getenv('DB_USER'),
            dbPassword: getenv('DB_PASSWORD'),
            dbPort: getenv('DB_PORT') ?: '3306',
            dbCharset: getenv('DB_CHARSET') ?: 'utf8mb4',
            driver: $pdoDriver,
            dbName: getenv('DB_NAME') ?: null,
        );
    }

    private static function createDoctrineFromEnv(string $sub): DoctrineDatabaseAdapter|PdoDatabaseAdapter
    {
        $isDev = (getenv('APP_ENV') ?: AppConfig::ENV_PRODUCTION) === AppConfig::ENV_DEVELOPMENT;

        if (!class_exists(\Doctrine\DBAL\DriverManager::class)) {
            $message = "Doctrine DBAL is not installed. Run: composer require doctrine/dbal.";

            if ($isDev) {
                throw new \RuntimeException($message);
            }

            error_log(sprintf(
                '[DatabaseFactory] %s | ENV=%s | driver=%s. Fallback to PDO.',
                $message,
                getenv('APP_ENV') ?: 'unknown',
                $sub
            ));

            return self::createPdoFromEnv($sub);
        }

        $connectionParams = [
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'driver' => $sub,
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        ];

        if ($dbName = getenv('DB_NAME')) {
            $connectionParams['dbname'] = $dbName;
        }

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

        return new DoctrineDatabaseAdapter($connection);
    }

    public static function createPdo(): PdoDatabaseAdapter
    {
        return self::createPdoFromEnv('pdo_mysql');
    }

    public static function createDoctrine(object $connection): DoctrineDatabaseAdapter
    {
        if (!($connection instanceof \Doctrine\DBAL\Connection)) {
            throw new \InvalidArgumentException(
                'Expected Doctrine\DBAL\Connection instance'
            );
        }

        return new DoctrineDatabaseAdapter($connection);
    }
}
