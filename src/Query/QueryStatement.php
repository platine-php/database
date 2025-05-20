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
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * @class QueryStatement
 *
 * @package Platine\Database\Query
 */
class QueryStatement
{
    /**
     * The where SQL parts
     * @var array<int, mixed>
     */
    protected array $wheres = [];

    /**
     * The having SQL parts
     * @var array<int, mixed>
     */
    protected array $having = [];

    /**
     * The join SQL parts
     * @var array<int, mixed>
     */
    protected array $joins = [];

    /**
     * The table SQL parts
     * @var array<int, mixed>
     */
    protected array $tables = [];

    /**
     * The columns SQL parts
     * @var array<int, mixed>
     */
    protected array $columns = [];

    /**
     * The order SQL parts
     * @var array<int, mixed>
     */
    protected array $order = [];

    /**
     * The group SQL parts
     * @var string[]|Expression[]
     */
    protected array $group = [];

    /**
     * The from SQL parts
     * @var array<int, string>
     */
    protected array $from = [];

    /**
     * The query placeholders values
     * @var array<int, mixed>
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
    public function addWhere(
        string|Closure|Expression $column,
        mixed $value,
        string $operator,
        string $separator
    ): void {
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
     *
     * @return void
     */
    public function addWhereLike(
        string|Closure|Expression $column,
        string $pattern,
        string $separator,
        bool $not
    ): void {
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
     *
     * @return void
     */
    public function addWhereBetween(
        string|Closure|Expression $column,
        mixed $value1,
        mixed $value2,
        string $separator,
        bool $not
    ): void {
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
     *
     * @return void
     */
    public function addWhereIn(
        string|Closure|Expression $column,
        mixed $value,
        string $separator,
        bool $not
    ): void {
        $columnName = $this->closureToExpression($column);

        if ($value instanceof Closure) {
            $select = new SubQuery();
            $value($select);

            $this->wheres[] = [
                'type' => 'whereInSelect',
                'column' => $columnName,
                'subquery' => $select,
                'separator' => $separator,
                'not' => $not
            ];
        } else {
            $this->wheres[] = [
                'type' => 'whereIn',
                'column' => $columnName,
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
     *
     * @return void
     */
    public function addWhereNull(
        string|Closure|Expression $column,
        string $separator,
        bool $not
    ): void {
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
     *
     * @return void
     */
    public function addWhereNop(
        string|Closure|Expression $column,
        string $separator
    ): void {
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
     *
     * @return void
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
     * @param string|array<int, string>|Closure $table
     * @param Closure|null $closure
     */
    public function addJoinClause(
        string $type,
        string|array|Closure $table,
        ?Closure $closure = null
    ): void {
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
     *
     * @return void
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
     *
     * @return void
     */
    public function addHaving(
        string|Closure|Expression $aggregate,
        mixed $value,
        string $operator,
        string $separator
    ): void {
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
     *
     * @return void
     */
    public function addHavingIn(
        string|Closure|Expression $aggregate,
        mixed $value,
        string $separator,
        bool $not
    ): void {
        $aggregateName = $this->closureToExpression($aggregate);

        if ($value instanceof Closure) {
            $select = new SubQuery();
            $value($select);

            $this->having[] = [
                'type' => 'havingInSelect',
                'aggregate' => $aggregateName,
                'subquery' => $select,
                'separator' => $separator,
                'not' => $not
            ];
        } else {
            $this->having[] = [
                'type' => 'havingIn',
                'aggregate' => $aggregateName,
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
     *
     * @return void
     */
    public function addHavingBetween(
        string|Closure|Expression $aggregate,
        mixed $value1,
        mixed $value2,
        string $separator,
        bool $not
    ): void {
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
     * @param array<int, string> $tables
     */
    public function addTables(array $tables): void
    {
        $this->tables = $tables;
    }

    /**
     * @param array<int|string, mixed> $columns
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
     *
     * @return void
     */
    public function addOrder(array $columns, string $order): void
    {
        foreach ($columns as &$column) {
            $column = $this->closureToExpression($column);
        }
        $orderValue = strtoupper($order);

        if (!in_array($orderValue, ['ASC', 'DESC'])) {
            $orderValue = 'ASC';
        }

        $this->order[] = [
            'columns' => $columns,
            'order' => $orderValue
        ];
    }

    /**
     * @param string[]|Expression[]|Closure[] $columns
     *
     * @return void
     */
    public function addGroupBy(array $columns): void
    {
        $cols = [];
        foreach ($columns as &$column) {
            $cols[] = $this->closureToExpression($column);
        }

        $this->group = $cols;
    }

    /**
     * @param string|Expression|Closure $column
     * @param string|null $alias
     *
     * @return void
     */
    public function addColumn(
        string|Expression|Closure $column,
        ?string $alias = null
    ): void {
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
     * @param array<int, string> $from
     */
    public function setFrom(array $from): void
    {
        $this->from = $from;
    }

    /**
     * @param mixed $value
     */
    public function addValue(mixed $value): void
    {
        $this->values[] = $this->closureToExpression($value);
    }

    /**
     * @return array<int, mixed>
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @return array<int, mixed>
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @return array<int, mixed>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return bool
     */
    public function hasDistinct(): bool
    {
        return $this->distinct;
    }

    /**
     * @return array<int, string>
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @return string[]|Expression[]
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
     * @return array<int, string>
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @return array<int, mixed>
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
    protected function closureToExpression(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return Expression::fromClosure($value);
        }

        return $value;
    }
}
