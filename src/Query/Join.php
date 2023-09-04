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
 *  @file Join.php
 *
 *  The Join class
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
 * Class Join
 * @package Platine\Database\Query
 */
class Join
{
    /**
     * The Join conditions
     * @var array<int, mixed>
     */
    protected array $conditions = [];

    /**
     * @return array<int, mixed>
     */
    public function getJoinConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param string|Expression|Closure $column1
     * @param string|Expression|Closure|bool|null $column2
     * @param string $operator
     * @return self
     */
    public function on($column1, $column2 = null, string $operator = '='): self
    {
        return $this->addJoinCondition($column1, $column2, $operator, 'AND');
    }

    /**
     * @param string|Expression|Closure $column1
     * @param string|Expression|Closure|bool|null $column2
     * @param string $operator
     * @return self
     */
    public function andOn($column1, $column2 = null, string $operator = '='): self
    {
        return $this->addJoinCondition($column1, $column2, $operator, 'AND');
    }

    /**
     * @param string|Expression|Closure $column1
     * @param string|Expression|Closure|bool|null $column2
     * @param string $operator
     * @return self
     */
    public function orOn($column1, $column2 = null, string $operator = '='): self
    {
        return $this->addJoinCondition($column1, $column2, $operator, 'OR');
    }

    /**
     * @param Expression|Closure $expression
     * @param string $separator
     * @return self
     */
    protected function addJoinExpression($expression, string $separator = 'AND'): self
    {
        if ($expression instanceof Closure) {
            $expression = Expression::fromClosure($expression);
        }

        $this->conditions[] = [
            'type' => 'joinExpression',
            'expression' => $expression,
            'separator' => $separator
        ];

        return $this;
    }

    /**
     * @param string|Expression|Closure $column1
     * @param string|Expression|Closure|bool|null $column2
     * @param string $operator
     * @param string $separator
     * @return self
     */
    protected function addJoinCondition(
        $column1,
        $column2,
        string $operator,
        string $separator = 'AND'
    ): self {
        if ($column1 instanceof Closure) {
            if ($column2 === true) {
                return $this->addJoinExpression($column1, $separator);
            }

            if ($column2 === null) {
                $join = new Join();
                $column1($join);

                $this->conditions[] = [
                    'type' => 'joinNested',
                    'join' => $join,
                    'separator' => $separator
                ];

                return $this;
            }
            $column1 = Expression::fromClosure($column1);
        } elseif (($column1 instanceof Expression) && $column2 === true) {
            return $this->addJoinExpression($column1, $separator);
        }

        if ($column2 instanceof Closure) {
            $column2 = Expression::fromClosure($column2);
        }

        $this->conditions[] = [
            'type' => 'joinColumn',
            'column1' => $column1,
            'column2' => $column2,
            'operator' => $operator,
            'separator' => $separator
        ];

        return $this;
    }
}
