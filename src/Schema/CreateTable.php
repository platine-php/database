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
 *  @file CreateTable.php
 *
 *  The create table schema class
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
 * Class CreateTable
 * @package Platine\Database\Schema
 */
class CreateTable
{
    /**
     * The list of CreateColumn
     * @var array<string, CreateColumn>
     */
    protected array $columns = [];

    /**
     * The primary or list of primary key
     * @var string|array<string, mixed>
     */
    protected $primaryKey;

    /**
     * The list of unique keys
     * @var array<string, array<int, mixed>>
     */
    protected array $uniqueKeys = [];

    /**
     * The list of indexes keys
     * @var array<string, array<int, mixed>>
     */
    protected array $indexes = [];

    /**
     * The list of ForeignKey
     * @var array<string, ForeignKey>
     */
    protected array $foreignKeys = [];

    /**
     * The name of the table
     * @var string
     */
    protected string $table;

    /**
     * The engine for the table
     * @var string|null
     */
    protected ?string $engine = null;

    /**
     * The auto increment name
     * @var bool|null
     */
    protected ?bool $autoincrement = null;

    /**
     * Class constructor
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
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
     * @return array<string, CreateColumn>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     *
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     *
     * @return array<string, array<int, mixed>>
     */
    public function getUniqueKeys(): array
    {
        return $this->uniqueKeys;
    }

    /**
     *
     * @return array<string, array<int, mixed>>
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     *
     * @return array<string, ForeignKey>
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     *
     * @return string|null
     */
    public function getEngine(): ?string
    {
        return $this->engine;
    }

    /**
     *
     * @return bool|null
     */
    public function getAutoincrement(): ?bool
    {
        return $this->autoincrement;
    }

    /**
     *
     * @param string|null $name
     * @return self
     */
    public function engine(?string $name): self
    {
        $this->engine = $name;

        return $this;
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return self
     */
    public function primary($columns, ?string $name = null): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($name === null) {
            $name = $this->table . '_pk_' . implode('_', $columns);
        }

        $this->primaryKey = [
            'name' => $name,
            'columns' => $columns
        ];

        return $this;
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return self
     */
    public function unique($columns, ?string $name = null): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($name === null) {
            $name = $this->table . '_uk_' . implode('_', $columns);
        }

        $this->uniqueKeys[$name] = $columns;

        return $this;
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return self
     */
    public function index($columns, ?string $name = null): self
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($name === null) {
            $name = $this->table . '_ik_' . implode('_', $columns);
        }

        $this->indexes[$name] = $columns;

        return $this;
    }

    /**
     *
     * @param string|array<int, string> $columns
     * @param string|null $name
     * @return ForeignKey
     */
    public function foreign($columns, ?string $name = null): ForeignKey
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($name === null) {
            $name = $this->table . '_fk_' . implode('_', $columns);
        }

        return $this->foreignKeys[$name] = new ForeignKey($columns);
    }

    /**
     *
     * @param CreateColumn $column
     * @param string|null $name
     * @return self
     */
    public function autoincrement(CreateColumn $column, ?string $name = null): self
    {
        if ($column->getType() !== 'integer') {
            return $this;
        }

        $this->autoincrement = true;

        $column->set('autoincrement', true);

        return $this->primary($column->getName(), $name);
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function integer(string $name): CreateColumn
    {
        return $this->addColumn($name, 'integer');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function float(string $name): CreateColumn
    {
        return $this->addColumn($name, 'float');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function double(string $name): CreateColumn
    {
        return $this->addColumn($name, 'double');
    }

    /**
     *
     * @param string $name
     * @param int|null $length
     * @param int|null $precision
     * @return CreateColumn
     */
    public function decimal(
        string $name,
        ?int $length = null,
        ?int $precision = null
    ): CreateColumn {
        return $this->addColumn($name, 'decimal')
                        ->length($length)
                        ->set('precision', $precision);
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function boolean(string $name): CreateColumn
    {
        return $this->addColumn($name, 'boolean');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function binary(string $name): CreateColumn
    {
        return $this->addColumn($name, 'binary');
    }

    /**
     *
     * @param string $name
     * @param int $length
     * @return CreateColumn
     */
    public function string(string $name, int $length = 255): CreateColumn
    {
        return $this->addColumn($name, 'string')
                        ->length($length);
    }

    /**
     *
     * @param string $name
     * @param int $length
     * @return CreateColumn
     */
    public function fixed(string $name, int $length = 255): CreateColumn
    {
        return $this->addColumn($name, 'fixed')
                        ->length($length);
    }

    /**
     *
     * @param string $name
     * @param array<mixed> $values
     * @return CreateColumn
     */
    public function enum(
        string $name,
        array $values
    ): CreateColumn {
        return $this->addColumn($name, 'enum')
                        ->set('values', $values);
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function text(string $name): CreateColumn
    {
        return $this->addColumn($name, 'text');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function time(string $name): CreateColumn
    {
        return $this->addColumn($name, 'time');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function timestamp(string $name): CreateColumn
    {
        return $this->addColumn($name, 'timestamp');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function date(string $name): CreateColumn
    {
        return $this->addColumn($name, 'date');
    }

    /**
     *
     * @param string $name
     * @return CreateColumn
     */
    public function datetime(string $name): CreateColumn
    {
        return $this->addColumn($name, 'datetime');
    }

    /**
     *
     * @param string $column
     * @return self
     */
    public function softDelete(string $column = 'deleted_at'): self
    {
        $this->datetime($column);

        return $this;
    }

    /**
     *
     * @param string $createColumn
     * @param string $updateColumn
     * @return self
     */
    public function timestamps(
        string $createColumn = 'created_at',
        string $updateColumn = 'updated_at'
    ): self {
        $this->datetime($createColumn)->notNull();
        $this->datetime($updateColumn);

        return $this;
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @return CreateColumn
     */
    protected function addColumn(string $name, string $type): CreateColumn
    {
        $column = new CreateColumn($this, $name, $type);
        $this->columns[$name] = $column;

        return $column;
    }
}
