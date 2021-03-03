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
 *  @file PostgreSQL.php
 *
 *  The PostgreSQL Driver class
 *
 *  @package    Platine\Database\Driver
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Driver;

use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\BaseColumn;
use Platine\Database\Schema\CreateTable;

/**
 * Class PostgreSQL
 * @package Platine\Database\Driver
 */
class PostgreSQL extends Driver
{

    /**
     * @inheritDoc
     */
    protected array $modifiers = [
        'nullable',
        'default'
    ];

    /**
     * @inheritDoc
     */
    public function getColumns(string $database, string $table): array
    {
        $sql = sprintf(
            'SELECT %s AS %s, %s AS %s FROM %s.%s WHERE %s = ? '
                . 'AND %s = ? ORDER BY %s ASC',
            $this->quoteIdentifier('column_name'),
            $this->quoteIdentifier('name'),
            $this->quoteIdentifier('udt_name'),
            $this->quoteIdentifier('type'),
            $this->quoteIdentifier('information_schema'),
            $this->quoteIdentifier('columns'),
            $this->quoteIdentifier('table_schema'),
            $this->quoteIdentifier('table_name'),
            $this->quoteIdentifier('ordinal_position'),
        );

        return [
            'sql' => $sql,
            'params' => [$database, $table]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseName(): array
    {
        return [
            'sql' => 'SELECT current_schema()',
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
     * @inheritdoc
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        $autoincrement = $column->get('autoincrement', false);
        $type = $autoincrement ? 'SERIAL' : 'INTEGER';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
            case 'small':
                $type = $autoincrement ? 'SMALLSERIAL' : 'SMALLINT';
                break;
            case 'medium':
                $type = $autoincrement ? 'SERIAL' : 'INTEGER';
                break;
            case 'big':
                $type = $autoincrement ? 'BIGSERIAL' : 'BIGINT';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeFloat(BaseColumn $column): string
    {
        return 'REAL';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDouble(BaseColumn $column): string
    {
        return 'DOUBLE PRECISION';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDecimal(BaseColumn $column): string
    {
        $type = 'DECIMAL';
        $length = $column->get('length');
        $precision = $column->get('pecision');
        if ($length !== null) {
            if ($precision === null) {
                $type = 'DECIMAL(' . $this->value($length) . ')';
            } else {
                $type = 'DECIMAL(' . $this->value($length) . ', '
                        . $this->value($precision) . ')';
            }
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeBinary(BaseColumn $column): string
    {
        return 'BYTEA';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeTime(BaseColumn $column): string
    {
        return 'TIME(0) WITHOUT TIME ZONE';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeTimestamp(BaseColumn $column): string
    {
        return 'TIMESTAMP(0) WITHOUT TIME ZONE';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDatetime(BaseColumn $column): string
    {
        return 'TIMESTAMP(0) WITHOUT TIME ZONE';
    }

    /**
     * @inheritdoc
     */
    protected function getIndexKeys(CreateTable $schema): array
    {
        $indexes = $schema->getIndexes();

        if (empty($indexes)) {
            return [];
        }

        $sql = [];
        $table = $schema->getTableName();

        foreach ($indexes as $name => $columns) {
            $sql[] = 'CREATE INDEX ' . $this->quoteIdentifier($table . '_' . $name)
                    . ' ON ' . $this->quoteIdentifier($table) . '(' . $this->quoteIdentifiers($columns) . ')';
        }

        return $sql;
    }

    /**
     * @inheritdoc
     */
    protected function getRenameColumn(AlterTable $schema, $data): string
    {
        $column = $data['column'];
        return sprintf(
            'ALTER TABLE %s RENAME COLUMN %s TO %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['from']),
            $this->quoteIdentifier($column->getName())
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAddIndex(AlterTable $schema, $data): string
    {
        return sprintf(
            'CREATE INDEX %s ON %s (%s)',
            $this->quoteIdentifier($schema->getTableName() . '_' . $data['name']),
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifiers($data['columns'])
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDropIndex(AlterTable $schema, $data): string
    {
        return sprintf(
            'DROP INDEX %s',
            $this->quoteIdentifier($schema->getTableName() . '_' . $data)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getEngine(CreateTable $schema): string
    {
        return '';
    }
}
