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
 *  @file HavingExpression.php
 *
 *  The class for Having Expression
 *
 *  @package    Platine\Database\Query
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;
use Platine\Database\Query\Expression;
use Platine\Database\Query\Having;
use Platine\Database\Query\QueryStatement;

/**
 * @class HavingExpression
 * @package Platine\Database\Query
 */
class HavingExpression
{
    /**
     * The Query statement instance
     * @var QueryStatement
     */
    protected QueryStatement $queryStatement;

    /**
     * The Having instance
     * @var Having
     */
    protected Having $having;

    /**
     * @var string|Expression
     */
    protected $column;

    /**
     * @var string
     */
    protected string $separator;

    /**
     * HavingExpression constructor.
     * @param QueryStatement $queryStatement
     */
    public function __construct(QueryStatement $queryStatement)
    {
        $this->queryStatement = $queryStatement;

        $this->having = new Having($queryStatement);
    }

    /**
     * @param string|Expression|Closure $column
     * @param string $separator
     * @return self
     */
    public function init($column, string $separator): self
    {
        if ($column instanceof Closure) {
            $column = Expression::fromClosure($column);
        }
        $this->column = $column;
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param bool $distinct
     * @return Having
     */
    public function count(bool $distinct = false): Having
    {
        $value = (new Expression())->count($this->column, $distinct);

        return $this->having->init($value, $this->separator);
    }

    /**
     * @param bool $distinct
     * @return Having
     */
    public function avg(bool $distinct = false): Having
    {
        $value = (new Expression())->avg($this->column, $distinct);

        return $this->having->init($value, $this->separator);
    }

    /**
     * @param bool $distinct
     * @return Having
     */
    public function min(bool $distinct = false): Having
    {
        $value = (new Expression())->min($this->column, $distinct);

        return $this->having->init($value, $this->separator);
    }

    /**
     * @param bool $distinct
     * @return Having
     */
    public function max(bool $distinct = false): Having
    {
        $value = (new Expression())->max($this->column, $distinct);

        return $this->having->init($value, $this->separator);
    }

    /**
     * @param bool $distinct
     * @return Having
     */
    public function sum(bool $distinct = false): Having
    {
        $value = (new Expression())->sum($this->column, $distinct);

        return $this->having->init($value, $this->separator);
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        if ($this->column instanceof Expression) {
            $this->column = clone $this->column;
        }
        $this->queryStatement = clone $this->queryStatement;
        $this->having = new Having($this->queryStatement);
    }
}
