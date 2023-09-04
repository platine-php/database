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
 *  @file SQLite.php
 *
 *  The SQLite Driver class
 *
 *  @package    Platine\Database\Driver
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Driver;

use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\BaseColumn;
use Platine\Database\Schema\CreateTable;

/**
 * Class SQLite
 * @package Platine\Database\Driver
 */
class SQLite extends Driver
{
    /**
     * @inheritdoc
     * @var string
     */
    protected string $identifier = '`%s`';

    /**
     * @inheritDoc
     */
    protected array $modifiers = [
        'nullable',
        'default',
        'autoincrement',
        'description'
    ];

    /**
     * @inheritDoc
     */
    protected string $autoincrement = 'AUTOINCREMENT';

    /**
     *
     * @var bool
     */
    private bool $noPrimaryKey = false;

    /**
     * @inheritDoc
     */
    public function getDatabaseName(): array
    {
        $dsn = $this->connection->getDsn();
        return [
            'result' => substr($dsn, strpos($dsn, ':') + 1)
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTables(string $database): array
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE type = ? '
                . ' ORDER BY %s ASC',
            $this->quoteIdentifier('name'),
            $this->quoteIdentifier('sqlite_master'),
            $this->quoteIdentifier('name'),
        );

        return [
            'sql' => $sql,
            'params' => ['table']
        ];
    }

    /**
     * @inheritDoc
     */
    public function getViews(string $database): array
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE type = ? '
                . ' ORDER BY %s ASC',
            $this->quoteIdentifier('name'),
            $this->quoteIdentifier('sqlite_master'),
            $this->quoteIdentifier('name'),
        );

        return [
            'sql' => $sql,
            'params' => ['view']
        ];
    }

    /**
     * @inheritDoc
     */
    public function getColumns(string $database, string $table): array
    {
        $sql = sprintf(
            'PRAGMA table_info(%s)',
            $this->quoteIdentifier($table)
        );

        return [
            'sql' => $sql,
            'params' => []
        ];
    }

    /**
     * @inheritDoc
     */
    public function getViewColumns(string $database, string $view): array
    {
        $sql = sprintf(
            'PRAGMA table_info(%s)',
            $this->quoteIdentifier($view)
        );

        return [
            'sql' => $sql,
            'params' => []
        ];
    }

    /**
     * @inheritdoc
     */
    public function renameTable(string $current, string $new): array
    {
        return [
            'sql' => 'ALTER TABLE ' . $this->quoteIdentifier($current)
            . ' RENAME TO ' . $this->quoteIdentifier($new),
            'params' => []
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        return 'INTEGER';
    }

    /**
     * @inheritDoc
     */
    protected function getTypeTime(BaseColumn $column): string
    {
        return 'DATETIME';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeEnum(BaseColumn $column): string
    {
        // TODO

        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getTypeTimestamp(BaseColumn $column): string
    {
        return 'DATETIME';
    }

    /**
     * @inheritDoc
     */
    protected function getModifierAutoincrement(BaseColumn $column): string
    {
        $modifier = parent::getModifierAutoincrement($column);

        if ($modifier !== '') {
            $this->noPrimaryKey = true;
            $modifier = 'PRIMARY KEY ' . $modifier;
        }

        return $modifier;
    }

    /**
     * @inheritDoc
     */
    protected function getPrimaryKey(CreateTable $schema): string
    {
        if ($this->noPrimaryKey) {
            return '';
        }
        return parent::getPrimaryKey($schema);
    }

    /**
     * @inheritdoc
     */
    protected function getEngine(CreateTable $schema): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function getAddUnique(AlterTable $schema, $data): string
    {
        return sprintf(
            'CREATE UNIQUE INDEX %s ON %s (%s)',
            $this->quoteIdentifier($data['name']),
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifiers($data['columns'])
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAddIndex(AlterTable $schema, $data): string
    {
        return sprintf(
            'CREATE INDEX %s ON %s (%s)',
            $this->quoteIdentifier($data['name']),
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifiers($data['columns'])
        );
    }

    /**
     * @inheritdoc
     */
    public function truncate(string $table): array
    {
        //TODO add a way to delete the table sequence information in
        //"sqlite_sequence table"
        //DELETE FROM `sqlite_sequence` WHERE `name` = 'TABLE_NAME';
        return [
            'sql' => 'DELETE FROM ' . $this->quoteIdentifier($table),
            'params' => []
        ];
    }
}
