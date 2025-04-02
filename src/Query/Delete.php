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
 *  @file Delete.php
 *
 *  The Delete statement class
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
use Platine\Database\Exception\QueryException;

/**
 * @class Delete
 * @package Platine\Database\Query
 */
class Delete extends DeleteStatement
{
    /**
     * @var Connection
     */
    protected Connection $connection;

    /**
     * Delete constructor.
     * @param Connection $connection
     * @param string|array<string> $from
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(
        Connection $connection,
        string|array $from,
        ?QueryStatement $queryStatement = null
    ) {
        parent::__construct($from, $queryStatement);

        $this->connection = $connection;
    }

    /**
     * Delete a record in database
     * @param string|array<string> $tables
     *
     * @return int
     * @throws QueryException
     */
    public function delete(string|array $tables = []): int
    {
        parent::delete($tables);
        $driver = $this->connection->getDriver();
        return $this->connection->count(
            $driver->delete($this->queryStatement),
            $driver->getParams()
        );
    }
}
