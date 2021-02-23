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
 *  @file Expression.php
 *
 *  The Expression class
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
 * Class Expression
 * @package Platine\Database\Query
 */
class Expression
{

    /**
     * The list of expression
     * @var array
     */
    protected array $expressions = [];

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function column($value): self
    {
        return $this->addExpression('column', $value);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function op($value): self
    {
        return $this->addExpression('op', $value);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function value($value): self
    {
        return $this->addExpression('value', $value);
    }

    /**
     * @param Closure $closure
     * @return $this
     */
    public function group(Closure $closure): self
    {
        $expression = new Expression();
        $closure($expression);

        return $this->addExpression('group', $expression);
    }

    /**
     * @param string|array $tables
     * @return SelectStatement
     */
    public function from($tables): SelectStatement
    {
        $subQuery = new SubQuery();
        $this->addExpression('subquery', $subQuery);

        return $subQuery->from($tables);
    }

    /**
     * @param string|array $column
     * @param bool $distinct
     * @return $this
     */
    public function count($column = '*', bool $distinct = false): self
    {
        if (!is_array($column)) {
            $column = [$column];
        }
        $distinct = $distinct || count($column) > 1;

        return $this->addFunction(
            'aggregateFunction',
            'COUNT',
            $column,
            ['distinct' => $distinct]
        );
    }

    /**
     * @param string $column
     * @param bool $distinct
     * @return $this
     */
    public function sum(string $column, bool $distinct = false): self
    {
        return $this->addFunction(
            'aggregateFunction',
            'SUM',
            $column,
            ['distinct' => $distinct]
        );
    }

    /**
     * @param string $column
     * @param bool $distinct
     * @return $this
     */
    public function avg(string $column, bool $distinct = false): self
    {
        return $this->addFunction(
            'aggregateFunction',
            'AVG',
            $column,
            ['distinct' => $distinct]
        );
    }

    /**
     * @param string $column
     * @param bool $distinct
     * @return $this
     */
    public function min(string $column, bool $distinct = false): self
    {
        return $this->addFunction(
            'aggregateFunction',
            'MIN',
            $column,
            ['distinct' => $distinct]
        );
    }

    /**
     * @param string $column
     * @param bool $distinct
     * @return $this
     */
    public function max(string $column, bool $distinct = false): self
    {
        return $this->addFunction(
            'aggregateFunction',
            'MAX',
            $column,
            ['distinct' => $distinct]
        );
    }

    /**
     * @param Closure $closure
     * @return self
     */
    public static function fromClosure(Closure $closure): self
    {
        $expression = new Expression();
        $closure($expression);

        return $expression;
    }

    /**
     * @param string $type
     * @param mixed $value
     *
     * @return $this
     */
    protected function addExpression(string $type, $value): self
    {
        $this->expressions[] = [
            'type' => $type,
            'value' => $value
        ];

        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string|Closure $column
     * @param array $arguments
     * @return $this
     */
    protected function addFunction(
        string $type,
        string $name,
        $column,
        array $arguments = []
    ): self {
        if ($column instanceof Closure) {
            $column = static::fromClosure($column);
        } elseif (is_array($column)) {
            foreach ($column as &$col) {
                if ($col instanceof Closure) {
                    $col = static::fromClosure($col);
                }
            }
        }

        $func = array_merge(
            [
                    'type' => $type,
                    'name' => $name,
                    'column' => $column
                ],
            $arguments
        );

        return $this->addExpression('function', $func);
    }
}
