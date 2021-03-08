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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * Class Where
 * @package Platine\Database\Query
 */
class Where
{

    /**
     * @var string|Expression
     */
    protected $column;

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
     * The Where statement instance
     * @var WhereStatement
     */
    protected WhereStatement $whereStatement;

    /**
     * Where constructor.
     * @param WhereStatement $whereStatement
     * @param QueryStatement $queryStatement
     */
    public function __construct(
        WhereStatement $whereStatement,
        QueryStatement $queryStatement
    ) {
        $this->queryStatement = $queryStatement;
        $this->whereStatement = $whereStatement;
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
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function is($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function eq($value, bool $isColumn = false): WhereStatement
    {
        return $this->is($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNot($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '!=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function neq($value, bool $isColumn = false): WhereStatement
    {
        return $this->isNot($value, $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function lt($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '<', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function gt($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '>', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function lte($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '<=', $isColumn);
    }

    /**
     * @param mixed $value
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    public function gte($value, bool $isColumn = false): WhereStatement
    {
        return $this->addCondition($value, '>=', $isColumn);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return WhereStatement|Select|Delete|Update
     */
    public function between($value1, $value2): WhereStatement
    {
        return $this->addBetweenCondition($value1, $value2, false);
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return WhereStatement|Select|Delete|Update
     */
    public function notBetween($value1, $value2): WhereStatement
    {
        return $this->addBetweenCondition($value1, $value2, true);
    }

    /**
     * @param mixed $value
     * @return WhereStatement|Select|Delete|Update
     */
    public function like($value): WhereStatement
    {
        return $this->addLikeCondition($value, false);
    }

    /**
     * @param mixed $value
     * @return WhereStatement|Select|Delete|Update
     */
    public function notLike($value): WhereStatement
    {
        return $this->addLikeCondition($value, true);
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return WhereStatement
     */
    public function in($value): WhereStatement
    {
        return $this->addInCondition($value, false);
    }

    /**
     * @param array<int, mixed>|Closure $value
     * @return WhereStatement
     */
    public function notIn($value): WhereStatement
    {
        return $this->addInCondition($value, true);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNull(): WhereStatement
    {
        return $this->addNullCondition(false);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function isNotNull(): WhereStatement
    {
        return $this->addNullCondition(true);
    }

    /**
     * @return WhereStatement|Select|Delete|Update
     */
    public function nop(): WhereStatement
    {
        $this->queryStatement->addWhereNop($this->column, $this->separator);

        return $this->whereStatement;
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
        $this->whereStatement = new WhereStatement($this->queryStatement);
    }

    /**
     * @param mixed $value
     * @param string $operator
     * @param bool $isColumn
     * @return WhereStatement|Select|Delete|Update
     */
    protected function addCondition(
        $value,
        string $operator,
        bool $isColumn = false
    ): WhereStatement {
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
    protected function addBetweenCondition($value1, $value2, bool $not): WhereStatement
    {
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
    protected function addLikeCondition(string $pattern, bool $not): WhereStatement
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
    protected function addInCondition($value, bool $not): WhereStatement
    {
        $this->queryStatement->addWhereIn($this->column, $value, $this->separator, $not);

        return $this->whereStatement;
    }

    /**
     * @param bool $not
     * @return WhereStatement|Select|Delete|Update
     */
    protected function addNullCondition(bool $not): WhereStatement
    {
        $this->queryStatement->addWhereNull($this->column, $this->separator, $not);

        return $this->whereStatement;
    }
}
