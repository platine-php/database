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
 *  @file CreateColumn.php
 *
 *  The create column schema class
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
 * Class CreateColumn
 * @package Platine\Database\Schema
 */
class CreateColumn extends BaseColumn
{

    /**
     * The associated table instance
     * @var CreateTable
     */
    protected CreateTable $table;

    /**
     * Class constructor
     * @param CreateTable $table
     * @param string $name
     * @param string|null $type
     */
    public function __construct(CreateTable $table, string $name, ?string $type = null)
    {
        $this->table = $table;
        parent::__construct($name, $type);
    }

    /**
     *
     * @return CreateTable
     */
    public function getTable(): CreateTable
    {
        return $this->table;
    }

    /**
     * Set the auto increment value
     * @param string|null $name
     * @return self
     */
    public function autoincrement(?string $name = null): self
    {
        $this->table->autoincrement($this, $name);

        return $this;
    }

    /**
     * Set the primary key
     * @param string|null $name
     * @return self
     */
    public function primary(?string $name = null): self
    {
        $this->table->primary($this->name, $name);

        return $this;
    }

    /**
     * Set the unique key
     * @param string|null $name
     * @return self
     */
    public function unique(?string $name = null): self
    {
        $this->table->unique($this->name, $name);

        return $this;
    }

    /**
     * Set the index key
     * @param string|null $name
     * @return self
     */
    public function index(?string $name = null): self
    {
        $this->table->index($this->name, $name);

        return $this;
    }
}
