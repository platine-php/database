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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Platine\Database\Connection;

/**
 * Class Query
 * @package Platine\Database\Query
 */
class Query extends BaseStatement
{

    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * @var string|array
     */
    protected $tables;

    /**
     * Query constructor.
     * @param Connection $connection
     * @param string|array $tables
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(Connection $connection, $tables, QueryStatement $queryStatement = null)
    {
        parent::__construct($queryStatement);

        $this->connection = $connection;
        $this->tables = $tables;
    }

    /**
     * @param bool $value
     *
     * @return Select|SelectStatement
     */
    public function distinct(bool $value = true)
    {
        return $this->buildSelect()->distinct($value);
    }

    /**
     * @param string|array|\Closure|Expression $columns
     *
     * @return Select|SelectStatement
     */
    public function groupBy($columns)
    {
        $this->buildSelect()->groupBy($columns);
    }

    /**
     * @param string|Expression $column
     *
     * @return Select|SelectStatement
     */
    public function having($column, \Closure $value = null)
    {
        $this->buildSelect()->having($column, $value);
    }

    /**
     * @param string|Expression $column
     *
     * @return Select|SelectStatement
     */
    public function orHaving($column, \Closure $value = null)
    {
        $this->buildSelect()->orHaving($column, $value);
    }

    /**
     * @param string|string[]|\Closure|\Closure[]|Expression|Expression[] $columns
     * @param string $order
     *
     * @return Select|SelectStatement
     */
    public function orderBy($columns, string $order = 'ASC')
    {
        return $this->buildSelect()->orderBy($columns, $order);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function limit(int $value)
    {
        return $this->buildSelect()->limit($value);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function offset(int $value)
    {
        return $this->buildSelect()->offset($value);
    }

    /**
     * @param string $table
     * @param string|null $database
     * @return Select|SelectStatement
     */
    public function into(string $table, string $database = null)
    {
        return $this->buildSelect()->into($table, $database);
    }

    /**
     * @param string|string[]|Expression|Expression[]|\Closure|\Closure[] $columns
     *
     * @return Select|SelectStatement
     */
    public function select($columns = [])
    {
        return $this->buildSelect()->select($columns);
    }

    /**
     * @param string|array $tables
     * @return int
     */
    public function delete($tables): int
    {
        return $this->buildDelete()->delete($tables);
    }

    /**
     * @param string|\Closure|Expression $name
     *
     * @return Select|SelectStatement
     */
    public function column($name)
    {
        return $this->buildSelect()->column($name);
    }

    /**
     * @param string|Expression|\Closure $column
     * @param bool $distinct
     *
     * @return int
     */
    public function count($column = '*', bool $distinct = false): int
    {
        return $this->buildSelect()->count($column, $distinct);
    }

    /**
     * @param string|Expression|\Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function avg($column, bool $distinct = false)
    {
        return $this->buildSelect()->avg($column, $distinct);
    }

    /**
     * @param string|Expression|\Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function sum($column, bool $distinct = false)
    {
        return $this->buildSelect()->sum($column, $distinct);
    }

    /**
     * @param string|Expression|\Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function min($column, bool $distinct = false)
    {
        return $this->buildSelect()->min($column, $distinct);
    }

    /**
     * @param string|Expression|\Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function max($column, bool $distinct = false)
    {
        return $this->buildSelect()->max($column, $distinct);
    }

    /**
     * @return Select
     */
    protected function buildSelect(): Select
    {
        return new Select($this->connection, $this->tables, $this->queryStatement);
    }

    /**
     * @return Delete
     */
    protected function buildDelete(): Delete
    {
        return new Delete($this->connection, $this->tables, $this->queryStatement);
    }
}
