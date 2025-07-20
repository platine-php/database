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
 *  @file Database.php
 *
 *  The Database class
 *
 *  @package    Platine\Database
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database;

use Platine\Database\Query\Query as QueryCommand;
use Platine\Database\Query\Insert as InsertCommand;
use Platine\Database\Query\Update as UpdateCommand;
use Platine\Database\Query\Delete as DeleteCommand;
use Platine\Database\Query\InsertStatement;

/**
 * @class QueryBuilder
 * @package Platine\Database
 */
class QueryBuilder
{
    /**
     * The Schema instance
     * @var Schema
     */
    protected Schema $schema;

    /**
     * Class constructor
     * @param Connection $connection
     */
    public function __construct(protected Connection $connection)
    {
        $this->schema = $this->connection->getSchema();
    }

    /**
     * Return the connection instance
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Return the instance of schema
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->schema;
    }

    /**
     * Shortcut to Connection::query
     * @param string $sql
     * @param array<int, mixed> $params
     * @return ResultSet
     */
    public function query(string $sql, array $params = []): ResultSet
    {
        return $this->connection->query($sql, $params);
    }

    /**
     * Shortcut to Connection::transaction
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback): mixed
    {
        return $this->connection->transaction($callback, $this);
    }

    /**
     * Execute a query in order to fetch or to delete records.
     * @param string|array<string> $tables Table name or an array of tables
     * @return QueryCommand
     */
    public function from(string|array $tables): QueryCommand
    {
        return new QueryCommand($this->connection, $tables);
    }

    /**
     * Insert new records into a table.
     * @param array<string, mixed> $values An array of values.
     * @return InsertCommand|InsertStatement
     */
    public function insert(array $values): InsertStatement
    {
        return (new InsertCommand($this->connection))->insert($values);
    }

    /**
     * Update records.
     * @param string $table
     * @return UpdateCommand
     */
    public function update(string $table): UpdateCommand
    {
        return new UpdateCommand($this->connection, $table);
    }

    /**
     * Delete records.
     * @param string $table
     * @return DeleteCommand
     */
    public function delete(string $table): DeleteCommand
    {
        return new DeleteCommand($this->connection, $table);
    }
}
