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
 *  @file SQLServer.php
 *
 *  The SQLServer Driver class
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

use Platine\Database\Query\QueryStatement;
use Platine\Database\Schema\BaseColumn;

/**
 * Class SQLServer
 * @package Platine\Database\Driver
 */
class SQLServer extends Driver
{

    /**
     * @inheritdoc
     * @var string
     */
    protected string $dateFormat = 'Y-m-d H:i:s.0000000';

    /**
     * @inheritdoc
     * @var string
     */
    protected string $identifier = '[%s]';

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
    protected string $autoincrement = 'IDENTITY';

    /**
     * @inheritDoc
     */
    public function select(QueryStatement $select): string
    {
        $limit = $select->getLimit();
        if ($limit <= 0) {
            return parent::select($select);
        }
        $offset = $select->getOffset();

        if ($offset < 0) {
            $sql = $select->getDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
            $sql .= 'TOP ' . $limit;
            $sql .= $this->getColumns($select->getColumns());
            $sql .= $this->getInto($select->getInt);
            $sql .= ' FROM ';
            $sql .= $this->getTables($select->getTables());
            $sql .= $this->getJoins($select->getJoins());
            $sql .= $this->getWheres($select->getWheres());
            $sql .= $this->getGroupBy($select->getGroupBy());
            $sql .= $this->getOrders($select->getOrder());
            $sql .= $this->getHaving($select->getHaving());

            return $sql;
        }

        $order = trim($this->getOrders($select->getOrder()));

        if (empty($order)) {
            $order = 'ORDER BY (SELECT 0)';
        }

        $sql = $select->getDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= $this->getColumns($select->getColumns());
        $sql .= ', ROW_NUMBER() OVER (' . $order . ') AS P_ROWNUM';
        $sql .= ' FROM ';
        $sql .= $this->getTables($select->getTables());
        $sql .= $this->getJoins($select->getJoins());
        $sql .= $this->getWheres($select->getWheres());
        $sql .= $this->getGroupBy($select->getGroupBy());
        $sql .= $this->getHaving($select->getHaving());

        $limit += $offset;
        $offset++;

        return sprintf(
            'SELECT * FROM (%s) AS A1 WHERE P_ROWNUM BETWEEN %d AND %d',
            $sql,
            $offset,
            $limit
        );
    }

    /**
     * @inheritDoc
     */
    public function update(QueryStatement $update): string
    {
        $joins = $this->getJoins($update->getJoins());
        $tables = $update->getTables();

        if ($joins !== '') {
            $joins = ' FROM ' . $this->getTables($tables) . ' ' . $joins;
            $tables = array_values($tables);
        }
        $sql = 'UPDATE ';
        $sql .= $this->getTables($tables);
        $sql .= $this->getSetColumns($update->getColumns());
        $sql .= $joins;
        $sql .= $this->getWheres($update->getWheres());

        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function renameTable(string $current, string $new): array
    {
        return [
            'sql' => 'sp_rename ' . $this->quoteIdentifier($current)
            . ', ' . $this->quoteIdentifier($new),
            'params' => []
        ];
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseName(): array
    {
        return [
            'sql' => 'SELECT SCHEMA_NAME()',
            'params' => []
        ];
    }

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
            $this->quoteIdentifier('data_type'),
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
     * @inheritdoc
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        $type = 'INTEGER';
        switch ($column->get('size', 'normal')) {
            case 'tiny':
                $type = 'TINYINT';
                break;
            case 'small':
                $type = 'SMALLINT';
                break;
            case 'medium':
                $type = 'INTEGER';
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
    protected function getTypeBoolean(BaseColumn $column): string
    {
        return 'BIT';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeString(BaseColumn $column): string
    {
        return 'NVARCHAR(' . $this->value($column->get('length', 255)) . ')';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeFixed(BaseColumn $column): string
    {
        return 'NCHAR(' . $this->value($column->get('length', 255)) . ')';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeText(BaseColumn $column): string
    {
        return 'NVARCHAR(max)';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeBinary(BaseColumn $column): string
    {
        return 'VARBINARY(max)';
    }

    /**
     * @inheritdoc
     */
    protected function getTypeTimestamp(BaseColumn $column): string
    {
        return 'DATETIME';
    }

    /**
     * @inheritdoc
     */
    protected function getRenameColumn(AlterTable $schema, $data): string
    {
        $column = $data['column'];
        return sprintf(
            'sp_rename %s.%s, %s, COLUMN',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['from']),
            $this->quoteIdentifier($column->getName())
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
