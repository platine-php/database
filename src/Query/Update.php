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
 *  @file Update.php
 *
 *  The Update statement class
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

use Platine\Database\Connection;

/**
 * @class Update
 * @package Platine\Database\Query
 */
class Update extends UpdateStatement
{
    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * Update constructor.
     * @param Connection $connection
     * @param string|array<int, string> $table
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(
        Connection $connection,
        string|array $table,
        ?QueryStatement $queryStatement = null
    ) {
        parent::__construct($table, $queryStatement);

        $this->connection = $connection;
    }

    /**
     * Update a record in database
     * @param array<int|string, mixed> $columns
     *
     * @return int
     */
    public function set(array $columns): int
    {
        parent::set($columns);
        $driver = $this->connection->getDriver();
        return $this->connection->count(
            $driver->update($this->queryStatement),
            $driver->getParams()
        );
    }

    /**
     * @param string|array<int|string, mixed> $column
     * @param mixed $value
     * @return int
     */
    public function increment(string|array $column, mixed $value = 1): int
    {
        return $this->incrementOrDecrement('+', $column, $value);
    }

    /**
     * @param string|array<int|string, mixed> $column
     * @param mixed $value
     * @return int
     */
    public function decrement(string|array $column, mixed $value = 1): int
    {
        return $this->incrementOrDecrement('-', $column, $value);
    }

    /**
     * @param string $sign
     * @param string|array<int|string, mixed> $columns
     * @param mixed $value
     * @return int
     */
    protected function incrementOrDecrement(
        string $sign,
        string|array $columns,
        mixed $value
    ): int {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $values = [];

        foreach ($columns as $key => $val) {
            if (is_numeric($key)) {
                $values[$val] = function (Expression $expr) use ($sign, $val, $value) {
                    $expr->column($val)->op($sign)->value($value);
                };
            } else {
                $values[$key] = function (Expression $expr) use ($sign, $key, $val) {
                    $expr->column($key)->op($sign)->value($val);
                };
            }
        }

        return $this->set($values);
    }
}
