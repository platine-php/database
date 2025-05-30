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
 *  @file Query.php
 *
 *  The database Query base class
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
use Platine\Database\Connection;
use Platine\Database\ResultSet;

/**
 * @class Query
 * @package Platine\Database\Query
 */
class Query extends BaseStatement
{
    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * @var string|array<string>
     */
    protected string|array $tables;

    /**
     * Query constructor.
     * @param Connection $connection
     * @param string|array<string> $tables
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(
        Connection $connection,
        string|array $tables,
        ?QueryStatement $queryStatement = null
    ) {
        parent::__construct($queryStatement);

        $this->connection = $connection;
        $this->tables = $tables;
    }

    /**
     * @param bool $value
     *
     * @return Select|SelectStatement
     */
    public function distinct(bool $value = true): Select|SelectStatement
    {
        return $this->buildSelect()->distinct($value);
    }

    /**
     * @param string|array<int, string>|Closure|Expression $columns
     *
     * @return Select|SelectStatement
     */
    public function groupBy(string|array|Closure|Expression $columns): Select|SelectStatement
    {
        return $this->buildSelect()->groupBy($columns);
    }

    /**
     * @param string|Expression $column
     * @param Closure|null $value
     *
     * @return Select|SelectStatement
     */
    public function having(string|Expression $column, ?Closure $value = null): Select|SelectStatement
    {
        return $this->buildSelect()->having($column, $value);
    }

    /**
     * @param string|Expression $column
     * @param Closure|null $value
     *
     * @return Select|SelectStatement
     */
    public function orHaving(string|Expression $column, ?Closure $value = null): Select|SelectStatement
    {
        return $this->buildSelect()->orHaving($column, $value);
    }

    /**
     * @param string|string[]|Closure|Closure[]|Expression|Expression[] $columns
     * @param string $order
     *
     * @return Select|SelectStatement
     */
    public function orderBy(
        string|array|Closure|Expression $columns,
        string $order = 'ASC'
    ): Select|SelectStatement {
        return $this->buildSelect()->orderBy($columns, $order);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function limit(int $value): Select|SelectStatement
    {
        return $this->buildSelect()->limit($value);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function offset(int $value): Select|SelectStatement
    {
        return $this->buildSelect()->offset($value);
    }

    /**
     * @param string $table
     * @return Select|SelectStatement
     */
    public function into(string $table): Select|SelectStatement
    {
        return $this->buildSelect()->into($table);
    }

    /**
     * @param string|string[]|Expression|Expression[]|Closure|Closure[] $columns
     *
     * @return ResultSet
     */
    public function select(string|array|Expression|Closure $columns = []): ResultSet
    {
        return $this->buildSelect()->select($columns);
    }

    /**
     * @param string|array<string> $tables
     * @return int
     */
    public function delete(string|array $tables): int
    {
        return $this->buildDelete()->delete($tables);
    }

    /**
     * @param string|Closure|Expression $name
     *
     * @return mixed
     */
    public function column(string|Closure|Expression $name): mixed
    {
        return $this->buildSelect()->column($name);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int
     */
    public function count(string|Expression|Closure $column = '*', bool $distinct = false): int
    {
        return $this->buildSelect()->count($column, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function avg(string|Expression|Closure $column, bool $distinct = false): int|float
    {
        return $this->buildSelect()->avg($column, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function sum(string|Expression|Closure $column, bool $distinct = false): int|float
    {
        return $this->buildSelect()->sum($column, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return mixed
     */
    public function min(string|Expression|Closure $column, bool $distinct = false): mixed
    {
        return $this->buildSelect()->min($column, $distinct);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return mixed
     */
    public function max(string|Expression|Closure $column, bool $distinct = false)
    {
        return $this->buildSelect()->max($column, $distinct);
    }

    /**
     * @return Select
     */
    protected function buildSelect(): Select
    {
        return new Select(
            $this->connection,
            $this->tables,
            $this->queryStatement
        );
    }

    /**
     * @return Delete
     */
    protected function buildDelete(): Delete
    {
        return new Delete(
            $this->connection,
            $this->tables,
            $this->queryStatement
        );
    }
}
