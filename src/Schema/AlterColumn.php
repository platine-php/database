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
 *  @file AlterColumn.php
 *
 *  The alter column schema class
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
 * Class AlterColumn
 * @package Platine\Database\Schema
 */
class AlterColumn extends BaseColumn
{

    /**
     * The alter table
     * @var AlterTable
     */
    protected AlterTable $table;

    /**
     *
     * @param AlterTable $table
     * @param string $name
     * @param string|null $type
     */
    public function __construct(AlterTable $table, string $name, ?string $type = null)
    {
        $this->table = $table;
        parent::__construct($name, $type);
    }

    /**
     *
     * @return AlterTable
     */
    public function getTable(): AlterTable
    {
        return $this->table;
    }

    /**
     * @inheritdoc
     */
    public function defaultValue($value): BaseColumn
    {
        if ($this->get('handleDefault', true)) {
            return parent::defaultValue($value);
        }

        return $this;
    }

    /**
     *
     * @return self
     */
    public function autoincrement(): self
    {
        return $this->set('autoincrement', true);
    }
}
