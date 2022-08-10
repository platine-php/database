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
 *  @file WhereStatement.php
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
 * Class WhereStatement
 * @package Platine\Database\Query
 */
class WhereStatement
{
    /**
     * The Query statement instance
     * @var QueryStatement
     */
    protected QueryStatement $queryStatement;

    /**
     * The Where instance
     * @var Where
     */
    protected Where $where;

    /**
     * WhereStatement constructor.
     * @param QueryStatement|null $queryStatement
     */
    public function __construct(QueryStatement $queryStatement = null)
    {
        if ($queryStatement === null) {
            $queryStatement = new QueryStatement();
        }
        $this->queryStatement = $queryStatement;
        $this->where = new Where($this, $this->queryStatement);
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
     * @param bool $isExpression
     *
     * @return WhereStatement|Where|Select|Delete|Update
     */
    public function where($column, bool $isExpression = false)
    {
        return $this->addWhereCondition($column, 'AND', $isExpression);
    }

    /**
     * @param string|Expression|Closure $column
     * @param bool $isExpression
     *
     * @return WhereStatement|Where|Select|Delete|Update
     */
    public function orWhere($column, bool $isExpression = false)
    {
        return $this->addWhereCondition($column, 'OR', $isExpression);
    }

    /**
     * @param Closure $select
     * @return WhereStatement|Select|Delete|Update
     */
    public function whereExists(Closure $select): self
    {
        return $this->addWhereExistsCondition($select, 'AND', false);
    }

    /**
     * @param Closure $select
     * @return WhereStatement|Select|Delete|Update
     */
    public function orWhereExists(Closure $select): self
    {
        return $this->addWhereExistsCondition($select, 'OR', false);
    }

    /**
     * @param Closure $select
     * @return WhereStatement|Select|Delete|Update
     */
    public function whereNotExists(Closure $select): self
    {
        return $this->addWhereExistsCondition($select, 'AND', true);
    }

    /**
     * @param Closure $select
     * @return WhereStatement|Select|Delete|Update
     */
    public function orWhereNotExists(Closure $select): self
    {
        return $this->addWhereExistsCondition($select, 'OR', true);
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->queryStatement = clone $this->queryStatement;
        $this->where = new Where($this, $this->queryStatement);
    }

    /**
     * @param mixed $column
     * @param string $separator
     * @param bool $isExpression
     *
     * @return WhereStatement|Where
     */
    protected function addWhereCondition($column, string $separator = 'AND', bool $isExpression = false)
    {
        if (($column instanceof Closure) && !$isExpression) {
            $this->queryStatement->addWhereGroup($column, $separator);

            return $this;
        }
        return $this->where->init($column, $separator);
    }


    /**
     * @param Closure $select
     * @param string $separator
     * @param bool $not
     * @return $this
     */
    protected function addWhereExistsCondition(
        Closure $select,
        string $separator = 'AND',
        bool $not = false
    ): self {
        $this->queryStatement->addWhereExists($select, $separator, $not);

        return $this;
    }
}
