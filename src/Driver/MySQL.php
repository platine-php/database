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
 *  @file MySQL.php
 *
 *  The MySQL Driver class
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

/**
 * @class MySQL
 * @package Platine\Database\Driver
 */
class MySQL extends Driver
{
    /**
     * @inheritdoc
     * @var string
     */
    protected string $identifier = '`%s`';

    /**
     * @inheritdoc
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        $type = 'INT';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
                $type = 'TINYINT';
                break;
            case 'small':
                $type = 'SMALLINT';
                break;
            case 'medium':
                $type = 'MEDIUMINT';
                break;
            case 'big':
                $type = 'BIGINT';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDecimal(BaseColumn $column): string
    {
        $type = 'DECIMAL';
        $length = $column->get('length');
        $precision = $column->get('precision');

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
    protected function getTypeEnum(BaseColumn $column): string
    {
        $type = 'ENUM';
        $values = $column->get('values');

        if (!empty($values)) {
            $values = array_map([$this, 'value'], $values);
            $type = 'ENUM(' . implode(',', $values) . ')';
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeBoolean(BaseColumn $column): string
    {
        return 'TINYINT(1)';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeText(BaseColumn $column): string
    {
        $type = 'TEXT';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
            case 'small':
                $type = 'TINYTEXT';
                break;
            case 'medium':
                $type = 'MEDIUMTEXT';
                break;
            case 'big':
                $type = 'LONGTEXT';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeBinary(BaseColumn $column): string
    {
        $type = 'BLOB';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
            case 'small':
                $type = 'TINYBLOB';
                break;
            case 'medium':
                $type = 'MEDIUMBLOB';
                break;
            case 'big':
                $type = 'LONGBLOB';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getDropPrimaryKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP PRIMARY KEY',
            $this->quoteIdentifier($schema->getTableName())
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDropUniqueKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP INDEX %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDropIndex(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP INDEX %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDropForeignKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP FOREIGN KEY %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getSetDefaultValue(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ALTER %s SET DEFAULT %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['column']),
            $this->value($data['value'])
        );
    }

    /**
     * @inheritdoc
     */
    protected function getDropDefaultValue(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ALTER %s DROP DEFAULT',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getRenameColumn(AlterTable $schema, $data): string
    {
        $tableName = $schema->getTableName();
        $columnName = $data['from'];
        /** @var BaseColumn  $column */
        $column = $data['column'];
        $newName = $column->getName();

        /** @var array<string, array<string, string>> $columns */
        $columns = $this->connection
                ->getSchema()
                ->getColumns($tableName, false, false);

        $columnType = 'integer';
        if (isset($columns[$columnName]) && isset($columns[$columnName]['type'])) {
            $columnType = $columns[$columnName]['type'];
        }

        return sprintf(
            'ALTER TABLE %s CHANGE %s %s %s',
            $this->quoteIdentifier($tableName),
            $this->quoteIdentifier($columnName),
            $this->quoteIdentifier($newName),
            $columnType
        );
    }
}
