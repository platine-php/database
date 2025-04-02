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
 *  @file HavingStatement.php
 *
 *  The class for Having query statement
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
 * @class HavingStatement
 * @package Platine\Database\Query
 */
class HavingStatement
{
    /**
     * The Query statement instance
     * @var QueryStatement
     */
    protected QueryStatement $queryStatement;

    /**
     * The instance of the HavingExpression
     * @var HavingExpression
     */
    protected HavingExpression $expression;

    /**
     * HavingStatement constructor.
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(?QueryStatement $queryStatement = null)
    {
        if ($queryStatement === null) {
            $queryStatement = new QueryStatement();
        }
        $this->queryStatement = $queryStatement;
        $this->expression = new HavingExpression($queryStatement);
    }

    /**
     * @return QueryStatement
     */
    public function getQueryStatement(): QueryStatement
    {
        return $this->queryStatement;
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $value
     * @return self
     */
    public function having(string|Expression|Closure $column, ?Closure $value = null): self
    {
        return $this->addCondition($column, $value, 'AND');
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $value
     * @return self
     */
    public function orHaving(string|Expression|Closure $column, ?Closure $value = null): self
    {
        return $this->addCondition($column, $value, 'OR');
    }

    /**
     * @inheritDoc
     */
    public function __clone(): void
    {
        $this->queryStatement = clone $this->queryStatement;
        $this->expression = new HavingExpression($this->queryStatement);
    }

    /**
     * @param string|Expression|Closure $column
     * @param Closure|null $value
     * @param string $separator
     * @return self
     */
    protected function addCondition(
        string|Expression|Closure $column,
        ?Closure $value = null,
        string $separator = 'AND'
    ): self {
        if (($column instanceof Closure) && $value === null) {
            $this->queryStatement->addHavingGroup($column, $separator);
        } else {
            $expr = $this->expression->init($column, $separator);
            if ($value) {
                $value($expr);
            }
        }

        return $this;
    }
}
