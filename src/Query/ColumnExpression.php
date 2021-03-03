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
 *  @file ColumnExpression.php
 *
 *  The class for column Expression
 *
 *  @package    Platine\Database\Query
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * Class ColumnExpression
 * @package Platine\Database\Query
 */
class ColumnExpression
{

    /**
     * The Query statement instance
     * @var QueryStatement
     */
    protected QueryStatement $queryStatement;

    /**
     * ColumnExpression constructor.
     * @param QueryStatement $queryStatement
     */
    public function __construct(QueryStatement $queryStatement)
    {
        $this->queryStatement = $queryStatement;
    }

    /**
     * @param string|Expression|Closure $name
     * @param string|null $alias
     * @return self
     */
    public function column($name, string $alias = null): self
    {
        $this->queryStatement->addColumn($name, $alias);

        return $this;
    }

    /**
     * Add multiple columns
     * @param array<int|string, mixed> $columns
     * @return self
     */
    public function columns(array $columns): self
    {
        foreach ($columns as $name => $alias) {
            if (!is_string($name)) {
                $this->column($alias, null);
                continue;
            }

            if (is_string($alias)) {
                $this->column($name, $alias);
            } else {
                $this->column($alias, $name);
            }
        }

        return $this;
    }

    /**
     * @param string|array<int, mixed>|Expression|Closure $column
     * @param string|null $alias
     * @param bool $distinct
     * @return self
     */
    public function count($column = '*', string $alias = null, bool $distinct = false): self
    {
        return $this->column((new Expression())->count($column, $distinct), $alias);
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     * @param bool $distinct
     * @return self
     */
    public function avg($column, string $alias = null, bool $distinct = false): self
    {
        return $this->column((new Expression())->avg($column, $distinct), $alias);
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     * @param bool $distinct
     * @return self
     */
    public function sum($column, string $alias = null, bool $distinct = false): self
    {
        return $this->column((new Expression())->sum($column, $distinct), $alias);
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     * @param bool $distinct
     * @return self
     */
    public function min($column, string $alias = null, bool $distinct = false): self
    {
        return $this->column((new Expression())->min($column, $distinct), $alias);
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     * @param bool $distinct
     * @return self
     */
    public function max($column, string $alias = null, bool $distinct = false): self
    {
        return $this->column((new Expression())->max($column, $distinct), $alias);
    }

    public function __clone()
    {
        $this->queryStatement = clone $this->queryStatement;
    }
}
