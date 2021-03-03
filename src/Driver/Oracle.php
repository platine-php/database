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
 *  @file Oracle.php
 *
 *  The Oracle Driver class
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

use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\BaseColumn;

/**
 * Class Oracle
 * @package Platine\Database\Driver
 */
class Oracle extends Driver
{

    /**
     * @inheritDoc
     */
    protected array $modifiers = [
        'nullable',
        'default',
        'autoincrement'
    ];

    /**
     * @inheritDoc
     */
    protected string $autoincrement = 'GENERATED BY DEFAULT ON NULL AS IDENTITY';

    /**
     * @inheritDoc
     */
    public function select(QueryStatement $select): string
    {
        $limit = $select->getLimit();
        if ($limit <= 0) {
            return parent::select($select);
        }

        $sql = $select->hasDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= $this->getColumnList($select->getColumns());
        $sql .= ' FROM ';
        $sql .= $this->getTableList($select->getTables());
        $sql .= $this->getJoins($select->getJoins());
        $sql .= $this->getWheres($select->getWheres());
        $sql .= $this->getGroupBy($select->getGroupBy());
        $sql .= $this->getHaving($select->getHaving());
        $sql .= $this->getOrders($select->getOrder());

        $offset = $select->getOffset();
        if ($offset < 0) {
            return sprintf(
                'SELECT * FROM (%s) A1 WHERE ROWNUM <= %s',
                $sql,
                $limit
            );
        }
        $limit += $offset;
        $offset++;
        return sprintf(
            'SELECT * FROM (SELECT A1.*, ROWNUM AS P_ROWNUM FROM (%s) A1 '
                . 'WHERE ROWNUM <= %d) WHERE P_ROWNUM >= %d',
            $sql,
            $limit,
            $offset
        );
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseName(): array
    {
        return [
            'sql' => 'SELECT user FROM dual',
            'params' => []
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTables(string $database): array
    {
        $sql = sprintf(
            'SELECT %s FROM %s WHERE owner = ? '
                . ' ORDER BY %s ASC',
            $this->quoteIdentifier('table_name'),
            $this->quoteIdentifier('all_tables'),
            $this->quoteIdentifier('table_name'),
        );

        return [
            'sql' => $sql,
            'params' => [$database]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getColumns(string $database, string $table): array
    {
        $sql = sprintf(
            'SELECT %s AS %s, %s AS %s FROM %s WHERE LOWER(%s) = ? '
                . 'AND LOWER(%s) = ? ORDER BY %s ASC',
            $this->quoteIdentifier('column_name'),
            $this->quoteIdentifier('name'),
            $this->quoteIdentifier('data_type'),
            $this->quoteIdentifier('type'),
            $this->quoteIdentifier('all_tab_columns'),
            $this->quoteIdentifier('owner'),
            $this->quoteIdentifier('table_name'),
            $this->quoteIdentifier('column_id'),
        );

        return [
            'sql' => $sql,
            'params' => [$database, $table]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function quoteIdentifier($value): string
    {
        if ($value instanceof Expression) {
            return $this->getExpressions($value->getExpressions());
        }

        $identifiers = [];

        foreach (explode('.', $value) as $segment) {
            if ($segment === '*') {
                $identifiers[] = $segment;
            } else {
                $identifiers[] = sprintf($this->identifier, strtoupper($segment));
            }
        }

        return implode('.', $identifiers);
    }

    /**
     * @inheritdoc
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        $type = 'NUMBER(10)';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
                $type = 'NUMBER(3)';
                break;
            case 'small':
                $type = 'NUMBER(5)';
                break;
            case 'medium':
                $type = 'NUMBER(7)';
                break;
            case 'big':
                $type = 'NUMBER(19)';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDouble(BaseColumn $column): string
    {
        return 'FLOAT(24)';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDecimal(BaseColumn $column): string
    {
        $type = 'NUMBER(10)';
        $length = $column->get('length');
        $precision = $column->get('pecision');
        if ($length !== null) {
            if ($precision === null) {
                $type = 'NUMBER(' . $this->value($length) . ')';
            } else {
                $type = 'NUMBER(' . $this->value($length) . ', '
                        . $this->value($precision) . ')';
            }
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeBoolean(BaseColumn $column): string
    {
        return 'NUMBER(1)';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeText(BaseColumn $column): string
    {
        $type = 'CLOB';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
            case 'small':
                $type = 'VARCHAR2(2000)';
                break;
            case 'medium':
            case 'big':
                $type = 'CLOB';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getTypeString(BaseColumn $column): string
    {
        return 'VARCHAR2(' . $this->value($column->get('length', 255)) . ')';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeTime(BaseColumn $column): string
    {
        return 'DATE';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeDatetime(BaseColumn $column): string
    {
        return 'DATE';
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
                $type = 'RAW(2000)';
                break;
            case 'medium':
            case 'big':
                $type = 'BLOB';
                break;
        }
        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getModifyColumn(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s MODIFY %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->getSchemaColumns([$data])
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAddColumn(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->getSchemaColumns([$data])
        );
    }

    /**
     * @inheritdoc
     */
    protected function getSetDefaultValue(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s MODIFY %s DEFAULT %s',
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
            'ALTER TABLE %s MODIFY %s DEFAULT NULL',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }
}
