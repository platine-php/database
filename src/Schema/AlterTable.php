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
 *  @file AlterTable.php
 *
 *  The alter table schema class
 *
 *  @package    Platine\Database\Schema
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Schema;

/**
 * @class AlterTable
 * @package Platine\Database\Schema
 */
class AlterTable
{
    /**
     * The list of commands
     * @var array<int, array<string, mixed>>
     */
    protected array $commands = [];

    /**
     * Class constructor
     * @param string $table The name of table
     */
    public function __construct(protected string $table)
    {
    }

    /**
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function dropIndex(string $name): self
    {
        return $this->addCommand('dropIndex', $name);
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function dropUnique(string $name): self
    {
        return $this->addCommand('dropUniqueKey', $name);
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function dropPrimary(string $name): self
    {
        return $this->addCommand('dropPrimaryKey', $name);
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function dropForeign(string $name): self
    {
        return $this->addCommand('dropForeignKey', $name);
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function dropColumn(string $name): self
    {
        return $this->addCommand('dropColumn', $name);
    }

    /**
     *
     * @param string $column
     * @return $this
     */
    public function dropDefaultValue(string $column): self
    {
        return $this->addCommand('dropDefaultValue', $column);
    }

    /**
     *
     * @param string $oldName
     * @param string $newName
     * @return $this
     */
    public function renameColumn(string $oldName, string $newName): self
    {
        return $this->addCommand('renameColumn', [
                    'from' => $oldName,
                    'column' => new AlterColumn($this, $newName)
        ]);
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return $this
     */
    public function primary(string|array $columns, ?string $name = null): self
    {
        return $this->addKey('addPrimary', $columns, $name);
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return $this
     */
    public function unique(string|array $columns, ?string $name = null): self
    {
        return $this->addKey('addUnique', $columns, $name);
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return $this
     */
    public function index(string|array $columns, ?string $name = null): self
    {
        return $this->addKey('addIndex', $columns, $name);
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return ForeignKey
     */
    public function foreign(string|array $columns, ?string $name = null): ForeignKey
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        if ($name === null) {
            $name = $this->table . '_fk_' . implode('_', $columns);
        }
        $foreign = new ForeignKey($columns);
        $this->addCommand('addForeign', [
            'name' => $name,
            'foreign' => $foreign
        ]);

        return $foreign;
    }

    /**
     *
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function setDefaultValue(string $column, mixed $value): self
    {
        return $this->addCommand('setDefaultValue', [
                    'column' => $column,
                    'value' => $value,
                ]);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function integer(string $name): AlterColumn
    {
        return $this->addColumn($name, 'integer');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function float(string $name): AlterColumn
    {
        return $this->addColumn($name, 'float');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function double(string $name): AlterColumn
    {
        return $this->addColumn($name, 'double');
    }

    /**
     *
     * @param string $name
     * @param int|null $length
     * @param int|null $precision
     * @return AlterColumn
     */
    public function decimal(
        string $name,
        ?int $length = null,
        ?int $precision = null
    ): AlterColumn {
        return $this->addColumn($name, 'decimal')
                    ->length($length)
                    ->set('precision', $precision);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function boolean(string $name): AlterColumn
    {
        return $this->addColumn($name, 'boolean');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function binary(string $name): AlterColumn
    {
        return $this->addColumn($name, 'binary');
    }

    /**
     *
     * @param string $name
     * @param int $length
     * @return AlterColumn
     */
    public function string(string $name, int $length = 255): AlterColumn
    {
        return $this->addColumn($name, 'string')
                    ->length($length);
    }

    /**
     *
     * @param string $name
     * @param int $length
     * @return AlterColumn
     */
    public function fixed(string $name, int $length = 255): AlterColumn
    {
        return $this->addColumn($name, 'fixed')
                    ->length($length);
    }

    /**
     *
     * @param string $name
     * @param array<mixed> $values
     * @return AlterColumn
     */
    public function enum(
        string $name,
        array $values
    ): AlterColumn {
        return $this->addColumn($name, 'enum')
                    ->set('values', $values);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function text(string $name): AlterColumn
    {
        return $this->addColumn($name, 'text');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function time(string $name): AlterColumn
    {
        return $this->addColumn($name, 'time');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function timestamp(string $name): AlterColumn
    {
        return $this->addColumn($name, 'timestamp');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function date(string $name): AlterColumn
    {
        return $this->addColumn($name, 'date');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function datetime(string $name): AlterColumn
    {
        return $this->addColumn($name, 'datetime');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toInteger(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'integer');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toFloat(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'float');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toDouble(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'double');
    }

    /**
     *
     * @param string $name
     * @param int|null $length
     * @param int|null $precision
     * @return AlterColumn
     */
    public function toDecimal(
        string $name,
        ?int $length = null,
        ?int $precision = null
    ): AlterColumn {
        return $this->modifyColumn($name, 'decimal')
                    ->length($length)
                    ->set('precision', $precision);
    }

    /**
     *
     * @param string $name
     * @param array<mixed> $values
     * @return AlterColumn
     */
    public function toEnum(
        string $name,
        array $values
    ): AlterColumn {
        return $this->modifyColumn($name, 'enum')
                    ->set('values', $values);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toBoolean(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'boolean');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toBinary(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'binary');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toString(string $name, int $length = 255): AlterColumn
    {
        return $this->modifyColumn($name, 'string')
                    ->length($length);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toFixed(string $name, int $length = 255): AlterColumn
    {
        return $this->modifyColumn($name, 'fixed')
                    ->length($length);
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toText(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'text');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toTime(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'time');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toTimestamp(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'timestamp');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toDate(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'date');
    }

    /**
     *
     * @param string $name
     * @return AlterColumn
     */
    public function toDatetime(string $name): AlterColumn
    {
        return $this->modifyColumn($name, 'datetime');
    }

    /**
     *
     * @param string $name
     * @param mixed $data
     * @return $this
     */
    protected function addCommand(string $name, mixed $data): self
    {
        $this->commands[] = [
            'type' => $name,
            'data' => $data
        ];

        return $this;
    }

    /**
     *
     * @param string $type
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return $this
     */
    protected function addKey(
        string $type,
        string|array $columns,
        ?string $name = null
    ): self {
        static $maps = [
            'addPrimary' => 'pk',
            'addUnique' => 'uk',
            'addForeignKey' => 'fk',
            'addIndex' => 'ik',
        ];

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($name === null) {
            $name = sprintf(
                '%s_%s_%s',
                $this->table,
                $maps[$type],
                implode('_', $columns)
            );
        }

        return $this->addCommand($type, [
            'name' => $name,
            'columns' => $columns
        ]);
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @return AlterColumn
     */
    protected function addColumn(string $name, string $type): AlterColumn
    {
        $column = new AlterColumn($this, $name, $type);
        $this->addCommand('addColumn', $column);

        return $column;
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @return AlterColumn
     */
    protected function modifyColumn(string $name, string $type): AlterColumn
    {
        $column = new AlterColumn($this, $name, $type);
        $column->set('handleDefault', false);
        $this->addCommand('modifyColumn', $column);

        return $column;
    }
}
