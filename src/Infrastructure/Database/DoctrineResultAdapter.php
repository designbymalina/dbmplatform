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

namespace Dbm\Infrastructure\Database;

use Dbm\Database\Contracts\ResultInterface;

class DoctrineResultAdapter implements ResultInterface
{
    private object $result;

    public function __construct(object $result)
    {
        $this->result = $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetch(): ?array
    {
        return $this->result->fetchAssociative() ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchAll(): array
    {
        return $this->result->fetchAllAssociative() ?: [];
    }
}
