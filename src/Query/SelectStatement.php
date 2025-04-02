<?php

/**
 * Platine Database
 *
 * Platine Database is the abstraction layer using PDO with support of query and schema builder
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Database
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file SelectStatement.php
 *
 *  The select statement class
 *
 *  @package    Platine\Database\Query
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * @class SelectStatement
 * @package Platine\Database\Query
 */
class SelectStatement extends BaseStatement
{
    /**
     * @var HavingStatement
     */
    protected HavingStatement $havingStatement;

    /**
     * SelectStatement constructor.
     * @param string|array<int, string> $tables
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(string|array $tables, ?QueryStatement $queryStatement = null)
    {
        parent::__construct($queryStatement);

        if (!is_array($tables)) {
            $tables = [$tables];
        }

        $this->queryStatement->addTables($tables);
        $this->havingStatement = new HavingStatement($this->queryStatement);
    }

    /**
     * @param string $table
     * @return $this
     */
    public function into(string $table): self
    {
        $this->queryStatement->setInto($table);

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function distinct(bool $value = true): self
    {
        $this->queryStatement->setDistinct($value);

        return $this;
    }

    /**
     * @param string|Expression|Closure|string[]|Expression[]|Closure[] $columns
     * @return $this
     */
    public function groupBy(string|Expression|Closure|array $columns): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->queryStatement->addGroupBy($columns);

        return $this;
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $closure
     * @return $this
     */
    public function having(string|Expression|Closure $column, ?Closure $closure = null): self
    {
        $this->havingStatement->having($column, $closure);

        return $this;
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $closure
     * @return $this
     */
    public function orHaving(
        string|Expression|Closure $column,
        ?Closure $closure = null
    ): self {
        $this->havingStatement->orHaving($column, $closure);

        return $this;
    }

    /**
     * @param string|Closure|Expression|string[]|Expression[]|Closure[] $columns
     * @param string $order
     * @return $this
     */
    public function orderBy(
        string|Closure|Expression|array $columns,
        string $order = 'ASC'
    ): self {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->queryStatement->addOrder($columns, $order);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function limit(int $value): self
    {
        $this->queryStatement->setLimit($value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function offset(int $value): self
    {
        $this->queryStatement->setOffset($value);

        return $this;
    }

    /**
     * @param string|Expression|Closure|string[]|Expression[]|Closure[] $columns
     * @return mixed
     */
    public function select(string|Expression|Closure|array $columns = []): mixed
    {
        $expr = new ColumnExpression($this->queryStatement);

        if ($columns instanceof Closure) {
            $columns($expr);
        } else {
            if (!is_array($columns)) {
                $columns = [$columns];
            }
            $expr->columns($columns);
        }

        return true;
    }

    /**
     * @param string|Expression|Closure $name
     * @return mixed
     */
    public function column(string|Expression|Closure $name): mixed
    {
        (new ColumnExpression($this->queryStatement))->column($name);

        return true;
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function count(
        string|Expression|Closure $column = '*',
        bool $distinct = false
    ): mixed {
        (new ColumnExpression($this->queryStatement))->count($column, null, $distinct);

        return true;
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function avg(
        string|Expression|Closure $column,
        bool $distinct = false
    ): mixed {
        (new ColumnExpression($this->queryStatement))->avg($column, null, $distinct);

        return true;
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function sum(string|Expression|Closure $column, bool $distinct = false): mixed
    {
        (new ColumnExpression($this->queryStatement))->sum($column, null, $distinct);

        return true;
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function min(string|Expression|Closure $column, bool $distinct = false): mixed
    {
        (new ColumnExpression($this->queryStatement))->min($column, null, $distinct);

        return true;
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function max(string|Expression|Closure $column, bool $distinct = false): mixed
    {
        (new ColumnExpression($this->queryStatement))->max($column, null, $distinct);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        parent::__clone();
        $this->havingStatement = new HavingStatement($this->queryStatement);
    }
}
