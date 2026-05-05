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

use Dbm\Database\Contracts\ExpressionBuilderInterface;

class DoctrineExpressionBuilderAdapter implements ExpressionBuilderInterface
{
    public function __construct(
        private object $expr
    ) {}

    public function and(string ...$conditions): string
    {
        return $this->expr->and(...$conditions);
    }

    public function or(string ...$conditions): string
    {
        return $this->expr->or(...$conditions);
    }

    public function eq(string $x, string $y): string
    {
        return $this->expr->eq($x, $y);
    }

    public function like(string $x, string $y): string
    {
        return $this->expr->like($x, $y);
    }

    public function in(string $x, string|array $y): string
    {
        return $this->expr->in($x, (array) $y);
    }
}
