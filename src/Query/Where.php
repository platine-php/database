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
 *  @file Where.php
 *
 *  The class for where query statement
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
 * @class Where
 * @package Platine\Database\Query
 */
class Where
{
    /**
     * @var string|Expression
     */
    protected string|Expression $column;

    /**
     * @var string
     */
    protected string $separator;

    /**
     * Where constructor.
     * @param WhereStatement $whereStatement
     * @param QueryStatement $queryStatement
     */
    public function __construct(
        protected WhereStatement $whereStatement,
        protected QueryStatement $queryStatement
    ) {
    }

    /**
     * @param string|Expression|Closure $column
     * @param string $separator
     * @return $this
     */
    public function init(string|Expression|Closure $column, string $separator): self
    {
        if ($column instanceof Closure) {
            $column = Expression::fromClosure($column);
        }
        $this->column = $column;
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param string $literal
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function literal(
        string $literal,
        mixed $value,
        bool $isColumn = false
    ): WhereStatement|Select|Delete|Update {
        return $this->addCondition($value, $literal, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function is(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function eq(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->is($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNot(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '!=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function neq(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->isNot($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function lt(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '<', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function gt(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '>', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function lte(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '<=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function gte(mixed $value, bool $isColumn = false): WhereStatement|Select|Delete|Update
    {
        return $this->addCondition($value, '>=', $isColumn);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return WhereStatement|Select|Delete|Update
     */
    public function between(mixed $value1, mixed $value2): WhereStatement|Select|Delete|Update
    {
        return $this->addBetweenCondition($value1, $value2, false);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return WhereStatement|Select|Delete|Update
     */
    public function notBetween(mixed $value1, mixed $value2): WhereStatement|Select|Delete|Update
    {
        return $this->addBetweenCondition($value1, $value2, true);
    }

    /**
     * @param mixed $value
     * @return WhereStatement|Select|Delete|Update
     */
    public function like(mixed $value): WhereStatement|Select|Delete|Update
    {
        return $this->addLikeCondition($value, false);
    }

    /**
     * @param mixed $value
     * @return WhereStatement|Select|Delete|Update
     */
    public function notLike(mixed $value): WhereStatement|Select|Delete|Update
    {
        return $this->addLikeCondition($value, true);
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return WhereStatement
     */
    public function in(array|Closure $value): WhereStatement
    {
        return $this->addInCondition($value, false);
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return WhereStatement
     */
    public function notIn(array|Closure $value): WhereStatement
    {
        return $this->addInCondition($value, true);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNull(): WhereStatement|Select|Delete|Update
    {
        return $this->addNullCondition(false);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNotNull(): WhereStatement|Select|Delete|Update
    {
        return $this->addNullCondition(true);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function nop(): WhereStatement|Select|Delete|Update
    {
        $this->queryStatement->addWhereNop($this->column, $this->separator);

        return $this->whereStatement;
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        if ($this->column instanceof Expression) {
            $this->column = clone $this->column;
        }
        $this->queryStatement = clone $this->queryStatement;
        $this->whereStatement = new WhereStatement($this->queryStatement);
    }

    /**
     * @param mixed $value
     * @param string $operator
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    protected function addCondition(
        mixed $value,
        string $operator,
        bool $isColumn = false
    ): WhereStatement|Select|Delete|Update {
        if ($isColumn && is_string($value)) {
            $value = function (Expression $expr) use ($value) {
                return $expr->column($value);
            };
        }
        $this->queryStatement->addWhere(
            $this->column,
            $value,
            $operator,
            $this->separator
        );

        return $this->whereStatement;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @param bool $not
     * @return WhereStatement|Select|Delete|update
     */
    protected function addBetweenCondition(
        mixed $value1,
        mixed $value2,
        bool $not
    ): WhereStatement|Select|Delete|update {
        $this->queryStatement->addWhereBetween(
            $this->column,
            $value1,
            $value2,
            $this->separator,
            $not
        );

        return $this->whereStatement;
    }

    /**
     * @param string $pattern
     * @param bool $not
     * @return WhereStatement|Select|Delete|update
     */
    protected function addLikeCondition(string $pattern, bool $not): WhereStatement|Select|Delete|update
    {
        $this->queryStatement->addWhereLike(
            $this->column,
            $pattern,
            $this->separator,
            $not
        );

        return $this->whereStatement;
    }

    /**
     * @param mixed $value
     * @param bool $not
     * @return WhereStatement|Select|Delete|update
     */
    protected function addInCondition(mixed $value, bool $not): WhereStatement|Select|Delete|update
    {
        $this->queryStatement->addWhereIn(
            $this->column,
            $value,
            $this->separator,
            $not
        );

        return $this->whereStatement;
    }

    /**
     * @param bool $not
     * @return WhereStatement|Select|Delete|Update
     */
    protected function addNullCondition(bool $not): WhereStatement|Select|Delete|Update
    {
        $this->queryStatement->addWhereNull(
            $this->column,
            $this->separator,
            $not
        );

        return $this->whereStatement;
    }
}
