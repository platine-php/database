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
 *  @file Select.php
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
use Platine\Database\Connection;
use Platine\Database\ResultSet;

/**
 * Class Select
 * @package Platine\Database\Query
 */
class Select extends SelectStatement
{

    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * Select constructor.
     * @param Connection $connection
     * @param string|array<int, string> $tables
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(
        Connection $connection,
        $tables,
        QueryStatement $queryStatement = null
    ) {
        parent::__construct($tables, $queryStatement);
        $this->connection = $connection;
    }

    /**
     * @param string|Expression|Closure|string[]|Expression[]|Closure[] $columns
     *
     * @return ResultSet
     */
    public function select($columns = [])
    {
        parent::select($columns);
        $driver = $this->connection->getDriver();

        return $this->connection->query(
            $driver->select($this->queryStatement),
            $driver->getParams()
        );
    }

    /**
     * @param string|Expression|Closure $name
     *
     * @return mixed|false
     */
    public function column($name)
    {
        parent::column($name);
        return $this->getColumnResult();
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int
     */
    public function count($column = '*', bool $distinct = false): int
    {
        parent::count($column, $distinct);
        return (int) $this->getColumnResult();
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function avg($column, bool $distinct = false)
    {
        parent::avg($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function sum($column, bool $distinct = false)
    {
        parent::sum($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function min($column, bool $distinct = false)
    {
        parent::min($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $distinct
     *
     * @return int|float
     */
    public function max($column, bool $distinct = false)
    {
        parent::max($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * Return the result set for column
     * @return mixed
     */
    protected function getColumnResult()
    {
        $driver = $this->connection->getDriver();

        return $this->connection->column(
            $driver->select($this->queryStatement),
            $driver->getParams()
        );
    }
}
