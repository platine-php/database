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
 *  @file QueryStatement.php
 *
 *  The Base class for SQL query statement
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
 * Class QueryStatement
 *
 * @package Platine\Database\Query
 */
class QueryStatement
{

    /**
     * The where SQL parts
     * @var array
     */
    protected array $wheres = [];

    /**
     * The having SQL parts
     * @var array
     */
    protected array $having = [];

    /**
     * The join SQL parts
     * @var array
     */
    protected array $joins = [];

    /**
     * The table SQL parts
     * @var array
     */
    protected array $tables = [];

    /**
     * The columns SQL parts
     * @var array
     */
    protected array $columns = [];

    /**
     * The order SQL parts
     * @var array
     */
    protected array $order = [];

    /**
     * The group SQL parts
     * @var array
     */
    protected array $group = [];

    /**
     * The from SQL parts
     * @var array
     */
    protected array $from = [];

    /**
     * The query placeholders values
     * @var array
     */
    protected array $values = [];

    /**
     * Whether need use distinct
     * @var bool
     */
    protected bool $distinct = false;

    /**
     * The limit value
     * @var int
     */
    protected int $limit = 0;

    /**
     * The offset value
     * @var int
     */
    protected int $offset = -1;

    /**
     * The insert table
     * @var string|null
     */
    protected ?string $intoTable = null;

    /**
     * @param Closure $callback
     * @param string $separator
     */
    public function addWhereGroup(Closure $callback, string $separator): void
    {
        $where = new WhereStatement();
        $callback($where);

        $this->wheres[] = [
            'type' => 'whereNested',
            'clause' => $where->getQueryStatement()->getWheres(),
            'separator' => $separator
        ];
    }

    /**
     * @param string|Closure|Expression $column
     * @param mixed $value
     * @param string $operator
     * @param string $separator
     */
    public function addWhere($column, $value, string $operator, string $separator): void
    {
        $this->wheres[] = [
            'type' => 'whereColumn',
            'column' => $this->closureToExpression($column),
            'value' => $this->closureToExpression($value),
            'operator' => $operator,
            'separator' => $separator
        ];
    }

    /**
     * @param string|Closure|Expression $column
     * @param string $pattern
     * @param string $separator
     * @param bool $not
     */
    public function addWhereLike($column, string $pattern, string $separator, bool $not): void
    {
        $this->wheres[] = [
            'type' => 'whereLike',
            'column' => $this->closureToExpression($column),
            'pattern' => $pattern,
            'separator' => $separator,
            'not' => $not
        ];
    }

    /**
     * @param string|Closure|Expression $column
     * @param mixed $value1
     * @param mixed $value2
     * @param string $separator
     * @param bool $not
     */
    public function addWhereBetween($column, $value1, $value2, string $separator, bool $not): void
    {
        $this->wheres[] = [
            'type' => 'whereBetween',
            'column' => $this->closureToExpression($column),
            'value1' => $this->closureToExpression($value1),
            'value2' => $this->closureToExpression($value2),
            'separator' => $separator,
            'not' => $not
        ];
    }

    /**
     * @param string|Closure|Expression $column
     * @param mixed $value
     * @param string $separator
     * @param bool $not
     */
    public function addWhereIn($column, $value, string $separator, bool $not): void
    {
        $column = $this->closureToExpression($column);

        if ($value instanceof Closure) {
            $select = new SubQuery();
            $value($select);

            $this->wheres[] = [
                'type' => 'whereInSelect',
                'column' => $column,
                'subquery' => $select,
                'separator' => $separator,
                'not' => $not
            ];
        } else {
            $this->wheres[] = [
                'type' => 'whereIn',
                'column' => $column,
                'value' => $value,
                'separator' => $separator,
                'not' => $not
            ];
        }
    }

    /**
     * @param string|Closure|Expression $column
     * @param string $separator
     * @param bool $not
     */
    public function addWhereNull($column, string $separator, bool $not): void
    {
        $this->wheres[] = [
            'type' => 'whereNull',
            'column' => $this->closureToExpression($column),
            'separator' => $separator,
            'not' => $not
        ];
    }

    /**
     * @param string|Closure|Expression $column
     * @param string $separator
     */
    public function addWhereNop($column, string $separator): void
    {
        $this->wheres[] = [
            'type' => 'whereNop',
            'column' => $column,
            'separator' => $separator
        ];
    }

    /**
     * @param Closure $closure
     * @param string $separator
     * @param bool $not
     */
    public function addWhereExists(Closure $closure, string $separator, bool $not): void
    {
        $select = new SubQuery();
        $closure($select);

        $this->wheres[] = [
            'type' => 'whereExists',
            'subquery' => $select,
            'separator' => $separator,
            'not' => $not
        ];
    }

    /**
     * @param string $type
     * @param string|array $table
     * @param Closure|null $closure
     */
    public function addJoinClause(string $type, $table, Closure $closure = null): void
    {
        $join = null;
        if ($closure !== null) {
            $join = new Join();
            $closure($join);
        }

        if ($table instanceof Closure) {
            $table = Expression::fromClosure($table);
        }

        if (!is_array($table)) {
            $table = [$table];
        }

        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'join' => $join
        ];
    }

    /**
     * @param Closure $closure
     * @param string $separator
     */
    public function addHavingGroup(Closure $closure, string $separator): void
    {
        $having = new HavingStatement();
        $closure($having);

        $this->having[] = [
            'type' => 'havingNested',
            'conditions' => $having->getQueryStatement()->getHaving(),
            'separator' => $separator
        ];
    }

    /**
     * @param string|Closure|Expression $aggregate
     * @param mixed $value
     * @param string $operator
     * @param string $separator
     */
    public function addHaving($aggregate, $value, string $operator, string $separator): void
    {
        $this->having[] = [
            'type' => 'havingCondition',
            'aggregate' => $this->closureToExpression($aggregate),
            'value' => $this->closureToExpression($value),
            'operator' => $operator,
            'separator' => $separator
        ];
    }

    /**
     * @param string|Closure|Expression $aggregate
     * @param mixed $value
     * @param string $separator
     * @param bool $not
     */
    public function addHavingIn($aggregate, $value, string $separator, bool $not): void
    {
        $aggregate = $this->closureToExpression($aggregate);

        if ($value instanceof Closure) {
            $select = new SubQuery();
            $value($select);

            $this->having[] = [
                'type' => 'havingInSelect',
                'aggregate' => $aggregate,
                'subquery' => $select,
                'separator' => $separator,
                'not' => $not
            ];
        } else {
            $this->having[] = [
                'type' => 'havingIn',
                'aggregate' => $aggregate,
                'value' => $value,
                'separator' => $separator,
                'not' => $not
            ];
        }
    }

    /**
     * @param string|Closure|Expression $aggregate
     * @param mixed $value1
     * @param mixed $value2
     * @param string $separator
     * @param bool $not
     */
    public function addHavingBetween($aggregate, $value1, $value2, string $separator, bool $not): void
    {
        $this->having[] = [
            'type' => 'havingBetween',
            'aggregate' => $this->closureToExpression($aggregate),
            'value1' => $this->closureToExpression($value1),
            'value2' => $this->closureToExpression($value2),
            'separator' => $separator,
            'not' => $not
        ];
    }

    /**
     * @param array $tables
     */
    public function addTables(array $tables): void
    {
        $this->tables = $tables;
    }

    /**
     * @param array $columns
     */
    public function addUpdateColumns(array $columns): void
    {
        foreach ($columns as $column => $value) {
            $this->columns[] = [
                'column' => $column,
                'value' => $this->closureToExpression($value)
            ];
        }
    }

    /**
     * @param string[]|Expression[]|Closure[] $columns
     * @param string $order
     */
    public function addOrder(array $columns, string $order): void
    {
        foreach ($columns as &$column) {
            $column = $this->closureToExpression($column);
        }
        $order = strtoupper($order);

        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'ASC';
        }

        $this->order[] = [
            'columns' => $columns,
            'order' => $order
        ];
    }

    /**
     * @param string[]|Expression[]|Closure[] $columns
     */
    public function addGroupBy(array $columns): void
    {
        foreach ($columns as &$column) {
            $column = $this->closureToExpression($column);
        }

        $this->group = $columns;
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     */
    public function addColumn($column, ?string $alias = null): void
    {
        $this->columns[] = [
            'name' => $this->closureToExpression($column),
            'alias' => $alias
        ];
    }

    /**
     * @param bool $value
     */
    public function setDistinct(bool $value): void
    {
        $this->distinct = $value;
    }

    /**
     * @param int $value
     */
    public function setLimit(int $value): void
    {
        $this->limit = $value;
    }

    /**
     * @param int $value
     */
    public function setOffset(int $value): void
    {
        $this->offset = $value;
    }

    /**
     * @param string $table
     */
    public function setInto(string $table): void
    {
        $this->intoTable = $table;
    }

    /**
     * @param array $from
     */
    public function setFrom(array $from): void
    {
        $this->from = $from;
    }

    /**
     * @param mixed $value
     */
    public function addValue($value): void
    {
        $this->values[] = $this->closureToExpression($value);
    }

    /**
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @return array
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return bool
     */
    public function getDistinct(): bool
    {
        return $this->distinct;
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getGroupBy(): array
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return string|null
     */
    public function getIntoTable(): ?string
    {
        return $this->intoTable;
    }

    /**
     * @return array
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|Expression
     */
    protected function closureToExpression($value)
    {
        if ($value instanceof Closure) {
            return Expression::fromClosure($value);
        }

        return $value;
    }
}
