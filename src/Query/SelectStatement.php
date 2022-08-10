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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * Class SelectStatement
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
    public function __construct($tables, QueryStatement $queryStatement = null)
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
     * @return self
     */
    public function into(string $table): self
    {
        $this->queryStatement->setInto($table);

        return $this;
    }

    /**
     * @param bool $value
     * @return self
     */
    public function distinct(bool $value = true): self
    {
        $this->queryStatement->setDistinct($value);

        return $this;
    }

    /**
     * @param string|Expression|Closure|string[]|Expression[]|Closure[] $columns
     * @return self
     */
    public function groupBy($columns): self
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
     * @return self
     */
    public function having($column, Closure $closure = null): self
    {
        $this->havingStatement->having($column, $closure);

        return $this;
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $closure
     * @return self
     */
    public function orHaving($column, Closure $closure = null): self
    {
        $this->havingStatement->orHaving($column, $closure);

        return $this;
    }

    /**
     * @param string|Closure|Expression|string[]|Expression[]|Closure[] $columns
     * @param string $order
     * @return self
     */
    public function orderBy($columns, string $order = 'ASC'): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->queryStatement->addOrder($columns, $order);

        return $this;
    }

    /**
     * @param int $value
     * @return self
     */
    public function limit(int $value): self
    {
        $this->queryStatement->setLimit($value);

        return $this;
    }

    /**
     * @param int $value
     * @return self
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
    public function select($columns = [])
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
    }

    /**
     * @param string|Expression|Closure $name
     * @return mixed
     */
    public function column($name)
    {
        (new ColumnExpression($this->queryStatement))->column($name);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function count($column = '*', bool $distinct = false)
    {
        (new ColumnExpression($this->queryStatement))->count($column, null, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function avg($column, bool $distinct = false)
    {
        (new ColumnExpression($this->queryStatement))->avg($column, null, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function sum($column, bool $distinct = false)
    {
        (new ColumnExpression($this->queryStatement))->sum($column, null, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function min($column, bool $distinct = false)
    {
        (new ColumnExpression($this->queryStatement))->min($column, null, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     * @return mixed
     */
    public function max($column, bool $distinct = false)
    {
        (new ColumnExpression($this->queryStatement))->max($column, null, $distinct);
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        parent::__clone();
        $this->havingStatement = new HavingStatement($this->queryStatement);
    }
}
