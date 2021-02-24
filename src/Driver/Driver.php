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
 *  @file Driver.php
 *
 *  The Database Query Driver class
 *
 *  Each driver like MySQL, SQLite, Oracle, etc. need extend this class
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

use Platine\Database\Connection;
use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\BaseColumn;
use Platine\Database\Schema\CreateTable;

/**
 * Class Driver
 *
 * @package Platine\Database\Driver
 */
class Driver
{

    /**
     * The driver default date format
     * @var string
     */
    protected string $dateFormat = 'Y-m-d H:i:s';

    /**
     * The quote identifier for a table and columns
     * @var string
     */
    protected string $identifier = '"%s"';

    /**
     * Each query separator
     * @var string
     */
    protected string $separator = ';';

    /**
     * The columns modifiers
     * @var array
     */
    protected array $modifiers = [
        'unsigned',
        'nullable',
        'default',
        'autoincrement'
    ];

    /**
     * Columns serial
     * @var array
     */
    protected array $serials = [
        'tiny',
        'small',
        'normal',
        'medium',
        'big'
    ];

    /**
     * Auto increment value modifier
     * @var string
     */
    protected string $autoincrement = 'AUTO_INCREMENT';

    /**
     * The query parameters
     * @var array
     */
    protected array $params = [];

    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
     * Class constructor
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns the SQL for SELECT statement
     * @param QueryStatement $select
     *
     * @return string
     */
    public function select(QueryStatement $select): string
    {
        $sql = $select->getDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $sql .= $this->getColumnList($select->getColumns());
        $sql .= $this->getInto($select->getIntoTable());
        $sql .= ' FROM ';
        $sql .= $this->getTableList($select->getTables());
        $sql .= $this->getJoins($select->getJoins());
        $sql .= $this->getWheres($select->getWheres());
        $sql .= $this->getGroupBy($select->getGroupBy());
        $sql .= $this->getOrders($select->getOrder());
        $sql .= $this->getHaving($select->getHaving());
        $sql .= $this->getLimit($select->getLimit());
        $sql .= $this->getOffset($select->getOffset());

        return $sql;
    }

    /**
     * Return SQL for INSERT statement
     * @param QueryStatement $insert
     *
     * @return string
     */
    public function insert(QueryStatement $insert): string
    {
        $columns = $this->getColumnList($insert->getColumns());

        $sql = 'INSERT INTO ';
        $sql .= $this->getTableList($insert->getTables());
        $sql .= ($columns == '*') ? '' : '(' . $columns . ')';
        $sql .= $this->getInsertValues($insert->getValues());

        return $sql;
    }

    /**
     * Return the SQL for UPDATE statement
     * @param QueryStatement $update
     *
     * @return string
     */
    public function update(QueryStatement $update): string
    {
        $sql = 'UPDATE ';
        $sql .= $this->getTableList($update->getTables());
        $sql .= $this->getJoins($update->getJoins());
        $sql .= $this->getSetColumns($update->getColumns());
        $sql .= $this->getWheres($update->getWheres());

        return $sql;
    }

    /**
     * Return the SQL for DELETE statement
     * @param QueryStatement $delete
     *
     * @return string
     */
    public function delete(QueryStatement $delete): string
    {
        $sql = 'DELETE ' . $this->getTableList($delete->getTables());
        $sql .= $sql === 'DELETE ' ? 'FROM ' : ' FROM ';
        $sql .= $this->getTableList($delete->getFrom());
        $sql .= $this->getJoins($delete->getJoins());
        $sql .= $this->getWheres($delete->getWheres());

        return $sql;
    }

    /**
     * Return the date format
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    /**
     * Set the drive options
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->{$name} = $value;
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function params(array $params): string
    {
        return implode(', ', array_map([$this, 'param'], $params));
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = $this->params;
        $this->params = [];

        return $params;
    }

    /**
     * @param array $columns
     *
     * @return string
     */
    public function columns(array $columns): string
    {
        return implode(', ', array_map([$this, 'quoteIdentifier'], $columns));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function quote(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    /**
     * Return the SQL for the current database
     * @return array
     */
    public function getDatabaseName(): array
    {
        return [
            'sql' => 'SELECT database()',
            'params' => []
        ];
    }

    /**
     *
     * @param string $current
     * @param string $new
     * @return array
     */
    public function renameTable(string $current, string $new): array
    {
        return [
            'sql' => 'RENAME TABLE ' . $this->quoteIdentifier($current)
            . ' TO ' . $this->quoteIdentifier($new),
            'params' => []
        ];
    }

    /**
     *
     * @param string $database
     * @return array
     */
    public function getTables(string $database): array
    {
        $sql = sprintf(
            'SELECT %s FROM %s.%s WHERE table_type = ? '
                . 'AND table_schema = ? ORDER BY %s ASC',
            $this->quoteIdentifier('table_name'),
            $this->quoteIdentifier('information_schema'),
            $this->quoteIdentifier('tables'),
            $this->quoteIdentifier('table_name'),
        );

        return [
            'sql' => $sql,
            'params' => ['BASE TABLE', $database]
        ];
    }

    /**
     *
     * @param string $database
     * @param string $table
     * @return array
     */
    public function getColumns(string $database, string $table): array
    {
        $sql = sprintf(
            'SELECT %s AS %s, %s AS %s FROM %s.%s WHERE %s = ? '
                . 'AND %s = ? ORDER BY %s ASC',
            $this->quoteIdentifier('column_name'),
            $this->quoteIdentifier('name'),
            $this->quoteIdentifier('column_type'),
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
     *
     * @param CreateTable $schema
     * @return array
     */
    public function create(CreateTable $schema): array
    {
        $sql = 'CREATE TABLE ' . $this->quoteIdentifier($schema->getTableName());
        $sql .= "(\n";
        $sql .= $this->getSchemaColumns($schema->getColumns());
        $sql .= $this->getPrimaryKey($schema);
        $sql .= $this->getUniqueKeys($schema);
        $sql .= $this->getForeignKeys($schema);
        $sql .= ")\n";
        $sql .= $this->getEngine($schema);

        $commands = [];

        $commands[] = [
            'sql' => $sql,
            'params' => []
        ];

        foreach ($this->getIndexKeys($schema) as $index) {
            $commands[] = [
                'sql' => $index,
                'params' => []
            ];
        }

        return $commands;
    }

    /**
     *
     * @param AlterTable $schema
     * @return array
     */
    public function alter(AlterTable $schema): array
    {
        $commands = [];

        foreach ($schema->getCommands() as $command) {
            $callback = 'get' . ucfirst($command['type']);
            $sql = $this->{$callback}($schema, $command['data']);

            if ($sql === '') {
                continue;
            }

            $commands[] = [
                'sql' => $sql,
                'params' => $this->getParams()
            ];
        }

        return $commands;
    }

    /**
     *
     * @param string $table
     * @return array
     */
    public function drop(string $table): array
    {
        return [
            'sql' => 'DROP TABLE ' . $this->quoteIdentifier($table),
            'params' => []
        ];
    }

    /**
     *
     * @param string $table
     * @return array
     */
    public function truncate(string $table): array
    {
        return [
            'sql' => 'TRUNCATE TABLE ' . $this->quoteIdentifier($table),
            'params' => []
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function param($value): string
    {
        if ($value instanceof Expression) {
            return $this->getExpressions($value->getExpressions());
        } elseif ($value instanceof DateTime) {
            $this->params[] = $value->format($this->dateFormat);
        } else {
            $this->params[] = $value;
        }

        return '?';
    }

    /**
     * Get the value by convert it to the type
     * @param mixed $value
     * @return mixed
     *
     */
    protected function value($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_string($value)) {
            return "'" . str_replace("'", "''", $value) . "'";
        }

        return 'NULL';
    }

    /**
     * Add quote identifier like "", ``
     * @param mixed $value
     *
     * @return string
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
                $identifiers[] = sprintf($this->identifier, $segment);
            }
        }

        return implode('.', $identifiers);
    }

    /**
     *
     * @param array $values
     * @param string $separator
     * @return string
     */
    protected function quoteIdentifiers(array $values, string $separator = ', '): string
    {
        return implode($separator, array_map([$this, 'quoteIdentifier'], $values));
    }

    /**
     * Handle expressions
     * @param array $expressions
     *
     * @return string
     */
    protected function getExpressions(array $expressions): string
    {
        $sql = [];

        foreach ($expressions as $expression) {
            switch ($expression['type']) {
                case 'column':
                    $sql[] = $this->quoteIdentifier($expression['value']);
                    break;
                case 'op':
                    $sql[] = $expression['value'];
                    break;
                case 'value':
                    $sql[] = $this->param($expression['value']);
                    break;
                case 'group':
                    $expr = $expression['value'];
                    $sql[] = '(' . $this->getExpressions($expr->getExpressions()) . ')';
                    break;
                case 'function':
                    $sql[] = $this->getSqlFunction($expression['value']);
                    break;
                case 'subquery':
                    $subQuery = $expression['value'];
                    $sql[] = '(' . $this->select($subQuery->getQueryStatement()) . ')';
                    break;
            }
        }

        return implode(' ', $sql);
    }

    /**
     * Handle SQL function
     * @param array $functions
     *
     * @return string
     */
    protected function getSqlFunction(array $functions): string
    {
        $method = $functions['type'] . $functions['name'];

        return $this->{$method}($functions);
    }

    /**
     * Handle columns
     * @param array $columns
     *
     * @return string
     */
    protected function getColumnList(array $columns): string
    {
        if (empty($columns)) {
            return '*';
        }
        $sql = [];

        foreach ($columns as $column) {
            if ($column['alias'] !== null) {
                $sql[] = $this->quoteIdentifier($column['name'])
                        . ' AS ' . $this->quoteIdentifier($column['alias']);
            } else {
                $sql[] = $this->quoteIdentifier($column['name']);
            }
        }

        return implode(', ', $sql);
    }

    /**
     * Handle schema columns
     * @param array $columns list of BaseColumn
     * @return string
     */
    protected function getSchemaColumns(array $columns): string
    {
        $sql = [];

        foreach ($columns as $column) {
            $line = $this->quoteIdentifier($column->getName());
            $line .= $this->getColumnType($column);
            $line .= $this->getColumnModifiers($column);

            $sql[] = $line;
        }

        return implode(",\n", $sql);
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getColumnType(BaseColumn $column): string
    {
        $callback = 'getType' . ucfirst($column->getType());
        $result = trim($this->{$callback}($column));

        if ($result !== '') {
            $result = ' ' . $result;
        }

        return $result;
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getColumnModifiers(BaseColumn $column): string
    {
        $line = '';
        foreach ($this->modifiers as $modifier) {
            $callback = 'getModifier' . ucfirst($modifier);
            $result = trim($this->{$callback}($column));

            if ($result !== '') {
                $result = ' ' . $result;
            }
            $line .= $line;
        }

        return $line;
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeInteger(BaseColumn $column): string
    {
        return 'INT';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeFloat(BaseColumn $column): string
    {
        return 'FLOAT';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeDouble(BaseColumn $column): string
    {
        return 'DOUBLE';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeDecimal(BaseColumn $column): string
    {
        return 'DECIMAL';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeBoolean(BaseColumn $column): string
    {
        return 'BOOLEAN';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeBinary(BaseColumn $column): string
    {
        return 'BLOB';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeText(BaseColumn $column): string
    {
        return 'TEXT';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeString(BaseColumn $column): string
    {
        return 'VARCHAR(' . $this->value($column->get('length', 355)) . ')';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeFixed(BaseColumn $column): string
    {
        return 'CHAR(' . $this->value($column->get('length', 355)) . ')';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeTime(BaseColumn $column): string
    {
        return 'TIME';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeTimestamp(BaseColumn $column): string
    {
        return 'TIMESTAMP';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeDate(BaseColumn $column): string
    {
        return 'DATE';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getTypeDatetime(BaseColumn $column): string
    {
        return 'DATETIME';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getModifierUnsigned(BaseColumn $column): string
    {
        return $column->get('unsigned', false) ? 'UNSIGNED' : '';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getModifierNullable(BaseColumn $column): string
    {
        return $column->get('nullable', true) ? '' : 'NOT NULL';
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getModifierDefault(BaseColumn $column): string
    {
        return $column->get('default', null) === null ? '' : 'DEFAULT ' . $this->value($column->get('default'));
    }

    /**
     *
     * @param BaseColumn $column
     * @return string
     */
    protected function getModifierAutoincrement(BaseColumn $column): string
    {
        if (
                $column->getType() !== 'integer' || !in_array($column->get('size', 'normal'), $this->serials)
        ) {
            return '';
        }
        return $column->get('autoincrement', false) ? $this->autoincrement : '';
    }

    /**
     *
     * @param CreateTable $schema
     * @return string
     */
    protected function getPrimaryKey(CreateTable $schema): string
    {
        $pk = $schema->getPrimaryKey();
        if ($pk === null) {
            return '';
        }

        return ",\n" . 'CONSTRAINT ' . $this->quoteIdentifier($pk['name'])
                . ' PRIMARY KEY (' . $this->quoteIdentifiers($pk['columns']) . ')';
    }

    /**
     *
     * @param CreateTable $schema
     * @return string
     */
    protected function getUniqueKeys(CreateTable $schema): string
    {
        $indexes = $schema->getUniqueKeys();

        if (empty($indexes)) {
            return '';
        }

        $sql = [];

        foreach ($indexes as $name => $columns) {
            $sql[] = 'CONSTRAINT ' . $this->quoteIdentifier($name)
                    . ' UNIQUE (' . $this->quoteIdentifiers($columns) . ')';
        }

        return ",\n" . implode(",\n", $sql);
    }

    /**
     *
     * @param CreateTable $schema
     * @return array
     */
    protected function getIndexKeys(CreateTable $schema): array
    {
        $indexes = $schema->getIndexes();

        if (empty($indexes)) {
            return [];
        }

        $sql = [];
        $table = $this->quoteIdentifier($schema->getTableName());

        foreach ($indexes as $name => $columns) {
            $sql[] = 'CREATE INDEX ' . $this->quoteIdentifier($name)
                    . ' ON ' . $table . '(' . $this->quoteIdentifiers($columns) . ')';
        }

        return $sql;
    }

    /**
     *
     * @param CreateTable $schema
     * @return string
     */
    protected function getForeignKeys(CreateTable $schema): string
    {
        $keys = $schema->getForeignKeys();

        if (empty($keys)) {
            return '';
        }

        $sql = [];

        foreach ($keys as $name => $key) {
            $cmd = 'CONSTRAINT ' . $this->quoteIdentifier($name)
                    . ' FOREIGN KEY (' . $this->quoteIdentifiers($key->getColumns()) . ') ';
            $cmd .= 'REFERENCES ' . $this->quoteIdentifier($key->getReferenceTable())
                    . ' (' . $this->quoteIdentifiers($key->getReferenceColumns()) . ')';

            foreach ($key->getActions() as $actionName => $action) {
                $cmd .= ' ' . $actionName . ' ' . $action;
            }
            $sql[] = $cmd;
        }

        return ",\n" . implode(",\n", $sql);
    }

    /**
     *
     * @param CreateTable $schema
     * @return string
     */
    protected function getEngine(CreateTable $schema): string
    {
        $engine = $schema->getEngine();
        if ($engine === null) {
            return '';
        }

        return ' ENGINE = ' . strtoupper($engine);
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropPrimaryKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP CONSTRAINT %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropUniqueKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP CONSTRAINT %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropIndex(AlterTable $schema, $data): string
    {
        return sprintf(
            'DROP INDEX %s.%s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropForeignKey(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP CONSTRAINT %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropColumn(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s DROP COLUMN %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getRenameColumn(AlterTable $schema, $data): string
    {
        //TODO: please implement it in subclass
        return '';
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getModifyColumn(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s MODIFY COLUMN %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->getSchemaColumns([$data])
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getAddColumn(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ADD COLUMN %s',
            $this->quoteIdentifier($schema->getTableName()),
            $this->getSchemaColumns([$data])
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getAddPrimary(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s PRIMARY KEY (%s)',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['name']),
            $this->quoteIdentifiers($data['columns'])
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getAddUnique(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s UNIQUE (%s)',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['name']),
            $this->quoteIdentifiers($data['columns'])
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
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
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getAddForeign(AlterTable $schema, $data): string
    {
        $key = $data['foreign'];
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['name']),
            $this->quoteIdentifiers($data['columns']),
            $this->quoteIdentifier($key->getReferenceTable()),
            $this->quoteIdentifiers($key->getReferenceColumns()),
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getSetDefaultValue(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ALTER COLUMN %s SET DEFAULT (%s)',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data['column']),
            $this->value($data['value'])
        );
    }

    /**
     *
     * @param AlterTable $schema
     * @param mixed $data
     * @return string
     */
    protected function getDropDefaultValue(AlterTable $schema, $data): string
    {
        return sprintf(
            'ALTER TABLE %s ALTER COLUMN %s DROP DEFAULT',
            $this->quoteIdentifier($schema->getTableName()),
            $this->quoteIdentifier($data)
        );
    }

    /**
     * Handle into the table
     *
     * @param string|null $table
     *
     * @return string
     */
    protected function getInto(?string $table): string
    {
        if ($table === null) {
            return '';
        }
        return ' INTO ' . $this->quoteIdentifier($table);
    }

    /**
     * Handle tables
     * @param array $tables
     *
     * @return string
     */
    protected function getTableList(array $tables): string
    {
        if (empty($tables)) {
            return '';
        }
        $sql = [];
        foreach ($tables as $name => $alias) {
            if (is_string($name)) {
                $sql[] = $this->quoteIdentifier($name) . ' AS ' . $this->quoteIdentifier($alias);
            } else {
                $sql[] = $this->quoteIdentifier($alias);
            }
        }

        return implode(', ', $sql);
    }

    /**
     * Handle for joins
     * @param array $joins
     *
     * @return string
     */
    protected function getJoins(array $joins): string
    {
        if (empty($joins)) {
            return '';
        }
        $sql = [];

        foreach ($joins as $join) {
            $joinObject = $join['join'];

            $on = '';
            if ($joinObject) {
                $on = $this->getJoinConditions($joinObject->getJoinConditions());
            }

            if ($on !== '') {
                $on = ' ON ' . $on;
            }

            $sql[] = $join['type'] . ' JOIN ' . $this->getTableList($join['table']) . $on;
        }

        return ' ' . implode(' ', $sql);
    }

    /**
     * Handle for the join conditions
     * @param array $conditions
     * @return string
     */
    protected function getJoinConditions(array $conditions): string
    {
        if (empty($conditions)) {
            return '';
        }
        $sql[] = $this->{$conditions[0]['type']}($conditions[0]);

        $count = count($conditions);
        for ($i = 1; $i < $count; $i++) {
            $sql[] = $conditions[$i]['separator'] . ' ' . $this->{$conditions[$i]['type']}($conditions[$i]);
        }

        return implode(' ', $sql);
    }

    /**
     * Handler where
     * @param array $wheres
     * @param bool $prefix
     *
     * @return string
     */
    protected function getWheres(array $wheres, bool $prefix = true): string
    {
        $sql = $this->getWheresHaving($wheres);
        if (empty($sql)) {
            return '';
        }
        return ($prefix ? ' WHERE ' : '') . $sql;
    }

    /**
     * Handle group by
     * @param array $groupBy
     *
     * @return string
     */
    protected function getGroupBy(array $groupBy): string
    {
        return empty($groupBy) ? '' : ' GROUP BY ' . $this->columns($groupBy);
    }

    /**
     * Handle for Order
     * @param array $orders
     * @return string
     */
    protected function getOrders(array $orders): string
    {
        if (empty($orders)) {
            return '';
        }
        $sql = [];
        foreach ($orders as $order) {
            $sql[] = $this->columns($order['columns']) . ' ' . $order['order'];
        }

        return ' ORDER BY ' . implode(', ', $sql);
    }

    /**
     * Handle columns for set (UPDATE)
     * @param array $columns
     * @return string
     */
    protected function getSetColumns(array $columns): string
    {
        if (empty($columns)) {
            return '';
        }
        $sql = [];

        foreach ($columns as $column) {
            $sql[] = $this->quoteIdentifier($column['column']) . ' = ' . $this->param($column['value']);
        }

        return ' SET ' . implode(', ', $sql);
    }

    /**
     * Handle for having
     * @param array $having
     * @param bool $prefix
     * @return string
     */
    protected function getHaving(array $having, bool $prefix = true): string
    {
        $sql = $this->getWheresHaving($having);
        if (empty($sql)) {
            return '';
        }
        return ($prefix ? ' HAVING ' : '') . $sql;
    }

    /**
     * Handle for insert values
     * @param array $values
     * @return string
     */
    protected function getInsertValues(array $values): string
    {
        return ' VALUES (' . $this->params($values) . ')';
    }

    /**
     * Return the build part for where or having
     * @param array $values
     *
     * @return string
     */
    protected function getWheresHaving(array $values): string
    {
        if (empty($values)) {
            return '';
        }

        $sql[] = $this->{$values[0]['type']}($values(0));
        $count = count($values);

        for ($i = 1; $i < $count; $i++) {
            $sql[] = $values[$i]['separator'] . ' ' . $this->{$values[$i]['type']}($values($i));
        }
        return implode(' ', $sql);
    }

    /**
     * Handle for limit
     * @param int $limit
     * @return string
     */
    protected function getLimit(int $limit): string
    {
        return ($limit === 0) ? '' : ' LIMIT ' . $this->param($limit);
    }

    /**
     * Handle for offset
     * @param int $offset
     * @return string
     */
    protected function getOffset(int $offset): string
    {
        return ($offset === 0) ? '' : ' OFFSET ' . $this->param($offset);
    }

    /**
     * @param array $join
     * @return string
     */
    protected function joinColumn(array $join): string
    {
        return sprintf(
            '%s %s %s',
            $this->quoteIdentifier($join['column1']),
            $join['operator'],
            $this->quoteIdentifier($join['column2'])
        );
    }

    /**
     * @param array $join
     * @return string
     */
    protected function joinNested(array $join): string
    {
        return '(' . $this->getJoinConditions($join['join']->getJoinConditions()) . ')';
    }

    /**
     * @param array $join
     * @return string
     */
    protected function joinExpression(array $join): string
    {
        return $this->quoteIdentifier($join['expression']);
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereColumn(array $where): string
    {
        return sprintf(
            '%s %s %s',
            $this->quoteIdentifier($where['column']),
            $where['operator'],
            $this->param($where['value'])
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereIn(array $where): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->quoteIdentifier($where['column']),
            $where['not'] ? 'NOT IN' : 'IN',
            $this->params($where['value'])
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereInSelect(array $where): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->quoteIdentifier($where['column']),
            $where['not'] ? 'NOT IN' : 'IN',
            $this->select($where['subquery']->getQueryStatement())
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereNested(array $where): string
    {
        return '(' . $this->getWheres($where['clause'], false) . ')';
    }

    /**
     * @param array $where
     * @return string
     */
    public function whereExists(array $where): string
    {
        return sprintf(
            '%s (%s)',
            $where['not'] ? 'NOT EXISTS' : 'EXISTS',
            $this->select($where['subquery']->getQueryStatement())
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereNull(array $where): string
    {
        return sprintf(
            '%s %s',
            $this->quoteIdentifier($where['column']),
            $where['not'] ? 'IS NOT NULL' : 'IS NULL',
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereBetween(array $where): string
    {
        return sprintf(
            '%s %s %s AND %s',
            $this->quoteIdentifier($where['column']),
            $where['not'] ? 'NOT BETWEEN' : 'BETWEEN',
            $this->param($where['value1']),
            $this->param($where['value2']),
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereLike(array $where): string
    {
        return sprintf(
            '%s %s %s',
            $this->quoteIdentifier($where['column']),
            $where['not'] ? 'NOT LIKE' : 'LIKE',
            $this->param($where['pattern']),
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereSubQuery(array $where): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->quoteIdentifier($where['column']),
            $where['operator'],
            $this->select($where['subquery']->getQueryStatement())
        );
    }

    /**
     * @param array $where
     * @return string
     */
    protected function whereNop(array $where): string
    {
        return $this->quoteIdentifier($where['column']);
    }

    /**
     * @param array $having
     * @return string
     */
    protected function havingCondition(array $having): string
    {
        return sprintf(
            '%s %s %s',
            $this->quoteIdentifier($having['aggregate']),
            $having['operator'],
            $having['value']
        );
    }

    /**
     * @param array $having
     * @return string
     */
    protected function havingNested(array $having): string
    {
        return '(' . $this->getHaving($having['conditions'], false) . ')';
    }

    /**
     * @param array $having
     * @return string
     */
    protected function havingBetween(array $having): string
    {
        return sprintf(
            '%s %s %s AND %s',
            $this->quoteIdentifier($having['aggregate']),
            $having['not'] ? 'NOT BETWEEN' : 'BETWEEN',
            $this->param($having['value1']),
            $this->param($having['value2']),
        );
    }

    /**
     * @param array $having
     * @return string
     */
    protected function havingInSelect(array $having): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->quoteIdentifier($having['aggregate']),
            $having['not'] ? 'NOT IN' : 'IN',
            $this->select($having['subquery']->getQueryStatement())
        );
    }

    /**
     * @param array $having
     * @return string
     */
    protected function havingIn(array $having): string
    {
        return sprintf(
            '%s %s (%s)',
            $this->quoteIdentifier($having['aggregate']),
            $having['not'] ? 'NOT IN' : 'IN',
            $this->params($having['value'])
        );
    }

    /**
     * Return aggregate function COUNT
     * @param array $function
     * @return string
     */
    protected function aggregateFunctionCOUNT(array $function): string
    {
        return sprintf(
            'COUNT(%s%s)',
            $function['distinct'] ? 'DISTINCT ' : '',
            $this->columns($function['column'])
        );
    }

    /**
     * Return aggregate function AVG
     * @param array $function
     * @return string
     */
    protected function aggregateFunctionAVG(array $function): string
    {
        return sprintf(
            'AVG(%s%s)',
            $function['distinct'] ? 'DISTINCT ' : '',
            $this->columns($function['column'])
        );
    }

    /**
     * Return aggregate function SUM
     * @param array $function
     * @return string
     */
    protected function aggregateFunctionSUM(array $function): string
    {
        return sprintf(
            'SUM(%s%s)',
            $function['distinct'] ? 'DISTINCT ' : '',
            $this->columns($function['column'])
        );
    }

    /**
     * Return aggregate function MIN
     * @param array $function
     * @return string
     */
    protected function aggregateFunctionMIN(array $function): string
    {
        return sprintf(
            'MIN(%s%s)',
            $function['distinct'] ? 'DISTINCT ' : '',
            $this->columns($function['column'])
        );
    }

    /**
     * Return aggregate function MAX
     * @param array $function
     * @return string
     */
    protected function aggregateFunctionMAX(array $function): string
    {
        return sprintf(
            'MAX(%s%s)',
            $function['distinct'] ? 'DISTINCT ' : '',
            $this->columns($function['column'])
        );
    }
}
