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
 *  @file ForeignKey.php
 *
 *  The foreign key schema class
 *
 *  @package    Platine\Database\Schema
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Schema;

/**
 * Class ForeignKey
 * @package Platine\Database\Schema
 */
class ForeignKey
{
    /**
     * The referenced table
     * @var string
     */
    protected string $referenceTable;

    /**
     * The referenced columns
     * @var string[]
     */
    protected array $referenceColumns = [];

    /**
     * The list of actions
     * @var array<string, string>
     */
    protected array $actions = [];

    /**
     * The base table columns
     * @var array<int, string>
     */
    protected array $columns = [];

    /**
     * Class constructor
     * @param array<int, string> $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     *
     * @return string
     */
    public function getReferenceTable(): string
    {
        return $this->referenceTable;
    }

    /**
     *
     * @return string[]
     */
    public function getReferenceColumns(): array
    {
        return $this->referenceColumns;
    }

    /**
     *
     * @return array<int, string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     *
     * @return array<string, string>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Set the references
     * @param string $table
     * @param string ...$columns
     * @return self
     */
    public function references(string $table, string ...$columns): self
    {
        $this->referenceTable = $table;
        $this->referenceColumns = $columns;

        return $this;
    }

    /**
     *
     * @param string $action
     * @return self
     */
    public function onDelete(string $action): self
    {
        return $this->addAction('ON DELETE', $action);
    }

    /**
     *
     * @param string $action
     * @return self
     */
    public function onUpdate(string $action): self
    {
        return $this->addAction('ON UPDATE', $action);
    }

    /**
     * Add the action
     * @param string $on the type of action DELETE, UPDATE
     * @param string $action
     * @return self
     */
    protected function addAction(string $on, string $action): self
    {
        $actionValue = strtoupper($action);
        if (!in_array($actionValue, ['RESTRICT', 'CASCADE', 'NO ACTION', 'SET NULL'])) {
            return $this;
        }

        $this->actions[$on] = $actionValue;

        return $this;
    }
}
