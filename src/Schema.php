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
 *  @file Schema.php
 *
 *  The database schema class
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

use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateTable;

/**
 * Class Schema
 * @package Platine\Database
 */
class Schema
{
    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
     * The list of tables
     * @var array<string, string>
     */
    protected array $tables = [];

    /**
     * The list of views
     * @var array<string, string>
     */
    protected array $views = [];

    /**
     * The current database name
     * @var string
     */
    protected string $databaseName = '';

    /**
     * The current table columns
     * @var array<string, array<string, array<string, string>>>
     */
    protected array $columns = [];

    /**
     * Class constructor
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        if ($this->databaseName === '') {
            $driver = $this->connection->getDriver();
            $result = $driver->getDatabaseName();

            if (isset($result['result'])) {
                $this->databaseName = $result['result'];
            } else {
                $this->databaseName = $this->connection->column(
                    $result['sql'],
                    $result['params']
                );
            }
        }
        return $this->databaseName;
    }

    /**
     * Check whether the given table exists
     * @param string $table
     * @param bool $skipCache
     * @return bool
     */
    public function hasTable(string $table, bool $skipCache = false): bool
    {
        $list = $this->getTables($skipCache);

        return isset($list[strtolower($table)]);
    }

    /**
     * Check whether the given view exists
     * @param string $view
     * @param bool $skipCache
     * @return bool
     */
    public function hasView(string $view, bool $skipCache = false): bool
    {
        $list = $this->getViews($skipCache);

        return isset($list[strtolower($view)]);
    }

    /**
     * Return the list of tables for the current database
     * @param bool $skipCache whether to use the cached data or not
     * @return array<string, string>
     */
    public function getTables(bool $skipCache = false): array
    {
        if ($skipCache) {
            $this->tables = [];
        }

        if (empty($this->tables)) {
            $driver = $this->connection->getDriver();
            $databaseName = $this->getDatabaseName();
            $sql = $driver->getTables($databaseName);

            $results = $this->connection
                                        ->query($sql['sql'], $sql['params'])
                                        ->fetchNum();

            while ($result = $results->next()) {
                $this->tables[strtolower($result[0])] = $result[0];
            }
        }

        return $this->tables;
    }

    /**
     * Return the list of views for the current database
     * @param bool $skipCache whether to use the cached data or not
     * @return array<string, string>
     */
    public function getViews(bool $skipCache = false): array
    {
        if ($skipCache) {
            $this->views = [];
        }

        if (empty($this->views)) {
            $driver = $this->connection->getDriver();
            $databaseName = $this->getDatabaseName();
            $sql = $driver->getViews($databaseName);

            $results = $this->connection
                                        ->query($sql['sql'], $sql['params'])
                                        ->fetchNum();

            while ($result = $results->next()) {
                $this->views[strtolower($result[0])] = $result[0];
            }
        }

        return $this->views;
    }

    /**
     * Return the list of columns for the given table
     * @param string $table
     * @param bool $skipCache
     * @param bool $names whether to return only the columns names
     * @return string[]|array<string, array<string, string>>
     */
    public function getColumns(
        string $table,
        bool $skipCache = false,
        bool $names = true
    ) {
        if ($skipCache) {
            unset($this->columns[$table]);
        }

        if (!$this->hasTable($table, $skipCache)) {
            return [];
        }

        if (!isset($this->columns[$table])) {
            $driver = $this->connection->getDriver();
            $databaseName = $this->getDatabaseName();
            $sql = $driver->getColumns($databaseName, $table);

            $results = $this->connection
                                        ->query($sql['sql'], $sql['params'])
                                        ->fetchAssoc();

            /** @var array<string, array<string, string>> $columns */
            $columns = [];

            while (
                    /** @var array<string, string>> $col */
                    $col = $results->next()
            ) {
                $columns[$col['name']] = [
                    'name' => $col['name'],
                    'type' => $col['type'],
                ];
            }

            $this->columns[$table] = $columns;
        }

        return $names ? array_keys($this->columns[$table]) : $this->columns[$table];
    }

    /**
     * Return the list of columns for the given view
     * @param string $view
     * @param bool $skipCache
     * @param bool $names whether to return only the columns names
     * @return string[]|array<string, array<string, string>>
     */
    public function getViewColumns(
        string $view,
        bool $skipCache = false,
        bool $names = true
    ) {
        if ($skipCache) {
            unset($this->columns[$view]);
        }

        if (!$this->hasView($view, $skipCache)) {
            return [];
        }

        if (!isset($this->columns[$view])) {
            $driver = $this->connection->getDriver();
            $databaseName = $this->getDatabaseName();
            $sql = $driver->getViewColumns($databaseName, $view);

            $results = $this->connection
                                        ->query($sql['sql'], $sql['params'])
                                        ->fetchAssoc();

            /** @var array<string, array<string, string>> $columns */
            $columns = [];

            while (
                    /** @var array<string, string>> $col */
                    $col = $results->next()
            ) {
                $columns[$col['name']] = [
                    'name' => $col['name'],
                    'type' => $col['type'],
                ];
            }

            $this->columns[$view] = $columns;
        }

        return $names ? array_keys($this->columns[$view]) : $this->columns[$view];
    }

    /**
     * Create new table
     * @param string $table
     * @param callable $callback callback to use to define the field(s) and indexes
     * @return void
     */
    public function create(string $table, callable $callback): void
    {
        $driver = $this->connection->getDriver();
        $schema = new CreateTable($table);
        $callback($schema);

        foreach ($driver->create($schema) as $result) {
            $this->connection->exec($result['sql'], $result['params']);
        }

        //clear all tables list
        $this->tables = [];
    }

    /**
     * Alter table definition
     * @param string $table
     * @param callable $callback callback to use to add/remove the field(s) or indexes
     * @return void
     */
    public function alter(string $table, callable $callback): void
    {
        $driver = $this->connection->getDriver();
        $schema = new AlterTable($table);
        $callback($schema);

        //clear all columns for this table
        unset($this->columns[strtolower($table)]);

        foreach ($driver->alter($schema) as $result) {
            $this->connection->exec($result['sql'], $result['params']);
        }
    }

    /**
     * Rename the table
     * @param string $table
     * @param string $newName
     * @return void
     */
    public function renameTable(string $table, string $newName): void
    {
        $driver = $this->connection->getDriver();
        $result = $driver->renameTable($table, $newName);
        $this->connection->exec($result['sql'], $result['params']);

        //clear all columns for this table
        unset($this->columns[strtolower($table)]);

        //clear all tables list
        $this->tables = [];
    }

    /**
     * Drop the table
     * @param string $table
     * @return void
     */
    public function drop(string $table): void
    {
        $driver = $this->connection->getDriver();
        $result = $driver->drop($table);
        $this->connection->exec($result['sql'], $result['params']);

        //clear all columns for this table
        unset($this->columns[strtolower($table)]);

        //clear all tables list
        $this->tables = [];
    }

    /**
     * Truncate the table
     * @param string $table
     * @return void
     */
    public function truncate(string $table): void
    {
        $driver = $this->connection->getDriver();
        $result = $driver->truncate($table);
        $this->connection->exec($result['sql'], $result['params']);
    }
}
