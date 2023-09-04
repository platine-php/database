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
 *  @file Having.php
 *
 *  The class for Havin query statement
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

/**
 * Class Having
 * @package Platine\Database\Query
 */
class Having
{
    /**
     * @var string|Expression
     */
    protected $aggregate;

    /**
     * @var string
     */
    protected string $separator;

    /**
     * The Query statement instance
     * @var QueryStatement
     */
    protected QueryStatement $queryStatement;

    /**
     * Having constructor.
     * @param QueryStatement $queryStatement
     */
    public function __construct(QueryStatement $queryStatement)
    {
        $this->queryStatement = $queryStatement;
    }

    /**
     * @param string|Expression|Closure $aggregate
     * @param string $separator
     * @return self
     */
    public function init($aggregate, string $separator): self
    {
        if ($aggregate instanceof Closure) {
            $aggregate = Expression::fromClosure($aggregate);
        }
        $this->aggregate = $aggregate;
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function is($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function eq($value, bool $isColumn = false): void
    {
        $this->is($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function isNot($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '!=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function neq($value, bool $isColumn = false): void
    {
        $this->isNot($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function lt($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '<', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function gt($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '>', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function lte($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '<=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return void
     */
    public function gte($value, bool $isColumn = false): void
    {
        $this->addCondition($value, '>=', $isColumn);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return void
     */
    public function between($value1, $value2): void
    {
        $this->queryStatement->addHavingBetween(
            $this->aggregate,
            $value1,
            $value2,
            $this->separator,
            false
        );
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return void
     */
    public function notBetween($value1, $value2): void
    {
        $this->queryStatement->addHavingBetween(
            $this->aggregate,
            $value1,
            $value2,
            $this->separator,
            true
        );
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return void
     */
    public function in($value): void
    {
        $this->queryStatement->addHavingIn(
            $this->aggregate,
            $value,
            $this->separator,
            false
        );
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return void
     */
    public function notIn($value): void
    {
        $this->queryStatement->addHavingIn(
            $this->aggregate,
            $value,
            $this->separator,
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        if ($this->aggregate instanceof Expression) {
            $this->aggregate = clone $this->aggregate;
        }
        $this->queryStatement = clone $this->queryStatement;
    }

    /**
     * @param mixed $value
     * @param string $operator
     * @param bool $isColumn
     * @return void
     */
    protected function addCondition($value, string $operator, bool $isColumn): void
    {
        if ($isColumn && is_string($value)) {
            $expr = new Expression();
            $value = $expr->column($value);
        }
        $this->queryStatement->addHaving(
            $this->aggregate,
            $value,
            $operator,
            $this->separator
        );
    }
}
