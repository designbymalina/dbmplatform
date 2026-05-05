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
use Dbm\Database\Contracts\SelectQueryBuilderInterface;

class DoctrineQueryBuilderAdapter implements SelectQueryBuilderInterface
{
    private object $qb;

    public function __construct(object $qb)
    {
        $this->qb = $qb;
    }

    public function select(string|array ...$cols): self
    {
        $this->qb->select(...$cols);
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->qb->from($table, $alias);
        return $this;
    }

    public function join(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->join($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function leftJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->leftJoin($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function rightJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->rightJoin($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function where(string $expr): self
    {
        $this->qb->where($expr);
        return $this;
    }

    public function andWhere(string $expr): self
    {
        $this->qb->andWhere($expr);
        return $this;
    }

    public function orderBy(string $sort, ?string $order = null): self
    {
        $this->qb->orderBy($sort, $order);
        return $this;
    }

    public function addOrderBy(string $sort, ?string $order = null): self
    {
        $this->qb->addOrderBy($sort, $order);
        return $this;
    }

    public function setMaxResults(int $limit): self
    {
        $this->qb->setMaxResults($limit);
        return $this;
    }

    public function setFirstResult(int $offset): self
    {
        $this->qb->setFirstResult($offset);
        return $this;
    }

    public function getSQL(): string
    {
        return $this->qb->getSQL();
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->qb->setParameter($key, $value);
        return $this;
    }

    public function setParameters(array $params): self
    {
        $this->qb->setParameters($params);
        return $this;
    }

    public function getParameters(): array
    {
        return $this->qb->getParameters();
    }

    public function expr(): ExpressionBuilderInterface
    {
        return new DoctrineExpressionBuilderAdapter(
            $this->qb->expr()
        );
    }
}
