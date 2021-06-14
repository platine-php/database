<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\HavingStatement;
use Platine\Database\Query\Join;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\SubQuery;
use Platine\Database\Query\WhereStatement;
use Platine\Dev\PlatineTestCase;

/**
 * QueryStatement class tests
 *
 * @group core
 * @group database
 */
class QueryStatementTest extends PlatineTestCase
{

    public function testAddWhereGroup(): void
    {
        $separator = 'AND';
        $closure = function (WhereStatement $w) {
            $w->where('foo')->is(1);
        };

        $e = new QueryStatement();

        $e->addWhereGroup($closure, $separator);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('clause', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);

        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals('whereNested', $r[0]['type']);
        $this->assertCount(1, $r[0]['clause']);
    }

    public function testAddWhere(): void
    {
        $column = 'foo';
        $value = 1;

        $this->addWhereTests($column, $value, false, false);

        $column = (new Expression())->column('foo');

        $this->addWhereTests($column, $value, true, false);

        $column = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addWhereTests($column, $value, true, false);

        $column = 'foo';

        $value = (new Expression())->column('foo');

        $this->addWhereTests($column, $value, false, true);

        $value = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addWhereTests($column, $value, false, true);
    }


    public function testAddWhereLike(): void
    {
        $column = 'foo';

        $this->addWhereLikeTests($column, true, false);
        $this->addWhereLikeTests($column, false, false);

        $column = (new Expression())->column('foo');

        $this->addWhereLikeTests($column, true, true);
        $this->addWhereLikeTests($column, false, true);

        $column = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addWhereLikeTests($column, true, true);
        $this->addWhereLikeTests($column, false, true);
    }


    public function testAddWhereNull(): void
    {
        $column = 'foo';

        $this->addWhereNullTests($column, true, false);
        $this->addWhereNullTests($column, false, false);

        $column = (new Expression())->column('foo');

        $this->addWhereNullTests($column, true, true);
        $this->addWhereNullTests($column, false, true);

        $column = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addWhereNullTests($column, true, true);
        $this->addWhereNullTests($column, false, true);
    }

    public function testAddWhereNop(): void
    {
        $columnString = 'foo';

        $this->addWhereNopTests($columnString);

        $columnExpr = (new Expression())->column('foo');
        $this->addWhereNopTests($columnExpr);

        $columnClosure = function (Expression $exp) {
            $exp->column('foo');
        };
        $this->addWhereNopTests($columnClosure);
    }

    public function testAddWhereExists(): void
    {
        $closure = function (SubQuery $sq) {
            $sq->from('foo')->column('bar');
        };

        $this->addWhereExistsTests($closure, true);
        $this->addWhereExistsTests($closure, false);
    }

    public function testAddJoinClause(): void
    {
        $closure = function (Join $j) {
            $j->andOn('foo');
        };
        $table = 'foo';

        $this->addJoinClauseTests($table, $closure, false, false);

        $table = function (Expression $ex) {
            $ex->column('bar');
        };

        $this->addJoinClauseTests($table, $closure, true, false);

        $closure = null;
        $table = 'foo';

        $this->addJoinClauseTests($table, $closure, false, true);
    }

    public function testAddWhereBetween(): void
    {
        $column = 'foo';
        $value1 = 1;
        $value2 = 3;

        $this->addWhereBetweenTests($column, $value1, $value2, true, false, false, false);
        $this->addWhereBetweenTests($column, $value1, $value2, false, false, false, false);

        $column = (new Expression())->column('foo');


        $this->addWhereBetweenTests($column, $value1, $value2, true, true, false, false);
        $this->addWhereBetweenTests($column, $value1, $value2, false, true, false, false);

        $column = function (Expression $exp) {
            $exp->column('foo');
        };


        $this->addWhereBetweenTests($column, $value1, $value2, true, true, false, false);
        $this->addWhereBetweenTests($column, $value1, $value2, false, true, false, false);

        $column = 'foo';

        $value1 = (new Expression())->column('foo');

        $this->addWhereBetweenTests($column, $value1, $value2, true, false, true, false);
        $this->addWhereBetweenTests($column, $value1, $value2, false, false, true, false);

        $value1 = function (Expression $exp) {
            $exp->column('foo');
        };

        $value2 = 3;

        $this->addWhereBetweenTests($column, $value1, $value2, true, false, true, false);
        $this->addWhereBetweenTests($column, $value1, $value2, false, false, true, false);

        $value2 = (new Expression())->column('foo');
        $value1 = 3;

        $this->addWhereBetweenTests($column, $value1, $value2, true, false, false, true);
        $this->addWhereBetweenTests($column, $value1, $value2, false, false, false, true);

        $value2 = function (Expression $exp) {
            $exp->column('foo');
        };

        $value1 = 3;

        $this->addWhereBetweenTests($column, $value1, $value2, true, false, false, true);
        $this->addWhereBetweenTests($column, $value1, $value2, false, false, false, true);
    }


    public function testAddWhereIn(): void
    {
        $column = 'foo';
        $value = 1;

        $this->addWhereInTests($column, $value, true, false, false);
        $this->addWhereInTests($column, $value, true, false, false);

        $column = (new Expression())->column('foo');

        $this->addWhereInTests($column, $value, true, true, false);
        $this->addWhereInTests($column, $value, true, true, false);

        $column = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addWhereInTests($column, $value, true, true, false);
        $this->addWhereInTests($column, $value, true, true, false);

        $column = 'foo';

        $value = function (SubQuery $sq) {
            $sq->from('foo')->column('bar');
        };

        $this->addWhereInTests($column, $value, true, false, true);
        $this->addWhereInTests($column, $value, true, false, true);
    }

    public function testAddHavingGroup(): void
    {
        $closure = function (HavingStatement $hs) {
            $hs->having('foo', function (HavingExpression $he) {
                $he->count()->is(1);
            });
        };

        $this->addHavingGroupTests($closure, 1);
    }

    public function testAddHaving(): void
    {
        $aggregate = 'foo';
        $value = 1;

        $this->addHavingTests($aggregate, $value, false, false);

        $aggregate = (new Expression())->column('foo');

        $this->addHavingTests($aggregate, $value, true, false);

        $aggregate = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addHavingTests($aggregate, $value, true, false);

        $aggregate = 'foo';

        $value = (new Expression())->column('foo');

        $this->addHavingTests($aggregate, $value, false, true);

        $value = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addHavingTests($aggregate, $value, false, true);
    }

    public function testAddHavingIn(): void
    {
        $aggregate = 'foo';
        $value = 1;

        $this->addHavingInTests($aggregate, $value, true, false, false);
        $this->addHavingInTests($aggregate, $value, false, false, false);

        $aggregate = (new Expression())->column('foo');

        $this->addHavingInTests($aggregate, $value, true, true, false);
        $this->addHavingInTests($aggregate, $value, false, true, false);

        $aggregate = function (Expression $exp) {
            $exp->column('foo');
        };

        $this->addHavingInTests($aggregate, $value, true, true, false);
        $this->addHavingInTests($aggregate, $value, false, true, false);

        $aggregate = 'foo';

        $value = (new Expression())->column('foo');

        $this->addHavingInTests($aggregate, $value, true, false, false);
        $this->addHavingInTests($aggregate, $value, false, false, false);

        $value = function (SubQuery $sq) {
            $sq->from('foo')->column('bar');
        };

        $this->addHavingInTests($aggregate, $value, true, false, true);
        $this->addHavingInTests($aggregate, $value, false, false, true);
    }

    public function testAddHavingBetween(): void
    {
        $aggregate = 'foo';
        $value1 = 1;
        $value2 = 3;

        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, false, false);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, false, false);

        $aggregate = (new Expression())->column('foo');


        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, true, false, false);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, true, false, false);

        $aggregate = function (Expression $exp) {
            $exp->column('foo');
        };


        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, true, false, false);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, true, false, false);

        $aggregate = 'foo';

        $value1 = (new Expression())->column('foo');

        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, true, false);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, false, true, false);

        $value1 = function (Expression $exp) {
            $exp->column('foo');
        };

        $value2 = 3;

        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, true, false);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, false, true, false);

        $value2 = (new Expression())->column('foo');
        $value1 = 3;

        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, false, true);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, false, false, true);

        $value2 = function (Expression $exp) {
            $exp->column('foo');
        };

        $value1 = 3;

        $this->addHavingBetweenTests($aggregate, $value1, $value2, true, false, false, true);
        $this->addHavingBetweenTests($aggregate, $value1, $value2, false, false, false, true);
    }

    public function testAddTables(): void
    {
        $tables = ['foo'];

        $e = new QueryStatement();

        $e->addTables($tables);

        $this->assertEquals($tables, $e->getTables());
    }

    public function testAddUpdateColumns(): void
    {
        $columns = [
            'foo' => 'bar',
            'baz' => (new Expression())->value(1)
        ];

        $e = new QueryStatement();

        $e->addUpdateColumns($columns);

        $cols = $e->getColumns();

        $this->assertCount(2, $cols);
        $this->assertArrayHasKey('foo', $columns);
        $this->assertArrayHasKey('baz', $columns);

        $this->assertEquals($columns['foo'], 'bar');
        $this->assertInstanceOf(Expression::class, $columns['baz']);
    }

    public function testAddOrder(): void
    {
        $columns = [
            'foo' => 'bar',
            'baz' => (new Expression())->value(1)
        ];

        $e = new QueryStatement();

        $e->addOrder($columns, 'ASC');

        $cols = $e->getOrder();

        $this->assertCount(1, $cols);
        $this->assertArrayHasKey('columns', $cols[0]);
        $this->assertArrayHasKey('order', $cols[0]);

        $this->assertCount(2, $cols[0]['columns']);
        $this->assertEquals($cols[0]['order'], 'ASC');
        $this->assertInstanceOf(Expression::class, $cols[0]['columns']['baz']);
    }

    public function testAddOrderWrongOrderValue(): void
    {
        $columns = [
            'foo' => 'bar',
            'baz' => (new Expression())->value(1)
        ];

        $e = new QueryStatement();

        $e->addOrder($columns, 'ACS');

        $cols = $e->getOrder();

        $this->assertCount(1, $cols);
        $this->assertArrayHasKey('columns', $cols[0]);
        $this->assertArrayHasKey('order', $cols[0]);

        $this->assertCount(2, $cols[0]['columns']);
        $this->assertEquals($cols[0]['order'], 'ASC');
        $this->assertInstanceOf(Expression::class, $cols[0]['columns']['baz']);
    }

    public function testAddGroupBy(): void
    {
        $columns = [
            'foo' => 'bar',
            'baz' => (new Expression())->value(1)
        ];

        $e = new QueryStatement();

        $e->addGroupBy($columns);

        $cols = $e->getGroupBy();

        $this->assertCount(2, $cols);
        $this->assertEquals($cols[0], 'bar');
        $this->assertInstanceOf(Expression::class, $cols[1]);
    }

    public function testAddColumnAliasIsNull(): void
    {
        $column = 'foo';

        $e = new QueryStatement();

        $e->addColumn($column);

        $cols = $e->getColumns();

        $this->assertCount(1, $cols);
        $this->assertArrayHasKey('name', $cols[0]);
        $this->assertArrayHasKey('alias', $cols[0]);
        $this->assertEquals($cols[0]['name'], $column);
        $this->assertNull($cols[0]['alias']);
    }

    public function testAddColumnAliasIsNotNull(): void
    {
        $column = 'foo';
        $alias = 'f';

        $e = new QueryStatement();

        $e->addColumn($column, $alias);

        $cols = $e->getColumns();

        $this->assertCount(1, $cols);
        $this->assertArrayHasKey('name', $cols[0]);
        $this->assertArrayHasKey('alias', $cols[0]);
        $this->assertEquals($cols[0]['name'], $column);
        $this->assertEquals($cols[0]['alias'], $alias);
    }

    public function testSetDistinct(): void
    {
        $value = true;

        $e = new QueryStatement();

        $e->setDistinct($value);

        $this->assertTrue($e->hasDistinct());

        $value = false;
        $e->setDistinct($value);

        $this->assertFalse($e->hasDistinct());
    }

    public function testSetLimitAndOffset(): void
    {
        $value = 10;

        $e = new QueryStatement();

        $this->assertEquals(-1, $e->getOffset());

        $e->setOffset($value);
        $this->assertEquals($value, $e->getOffset());

        $e->setLimit($value);
        $this->assertEquals($value, $e->getLimit());
    }

    public function testSetIntoTable(): void
    {
        $value = 'foo';

        $e = new QueryStatement();

        $e->setInto($value);
        $this->assertEquals($value, $e->getIntoTable());
    }

    public function testSetFrom(): void
    {
        $value = ['foo'];

        $e = new QueryStatement();

        $e->setFrom($value);
        $this->assertEquals($value, $e->getFrom());
    }

    public function testAddValue(): void
    {
        $foo = 'bar';
        $baz = (new Expression())->value(1);

        $e = new QueryStatement();

        $e->addValue($foo);

        $cols = $e->getValues();
        $this->assertCount(1, $cols);
        $this->assertEquals($cols[0], 'bar');

        $e->addValue($baz);

        $cols = $e->getValues();
        $this->assertCount(2, $cols);
        $this->assertInstanceOf(Expression::class, $cols[1]);
    }

    private function addHavingBetweenTests(
        $aggregate,
        $value1,
        $value2,
        $not,
        $columnIsClosureOrExpression = false,
        $value1IsClosureOrExpression = false,
        $value2IsClosureOrExpression = false
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addHavingBetween($aggregate, $value1, $value2, $separator, $not);

        $r = $e->getHaving();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('aggregate', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);
        $this->assertArrayHasKey('value1', $r[0]);
        $this->assertArrayHasKey('value2', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($not, $r[0]['not']);
        $this->assertEquals('havingBetween', $r[0]['type']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['aggregate']);
        } else {
            $this->assertEquals($aggregate, $r[0]['aggregate']);
        }

        if ($value1IsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value1']);
        } else {
            $this->assertEquals($value1, $r[0]['value1']);
        }

        if ($value2IsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value2']);
        } else {
            $this->assertEquals($value2, $r[0]['value2']);
        }
    }

    private function addHavingInTests(
        $aggregate,
        $value,
        $not,
        $aggregateIsClosureOrExpression = false,
        $valueIsClosure = false
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addHavingIn($aggregate, $value, $separator, $not);

        $r = $e->getHaving();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('aggregate', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);

        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($not, $r[0]['not']);

        if ($aggregateIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['aggregate']);
        } else {
            $this->assertEquals($aggregate, $r[0]['aggregate']);
        }

        if ($valueIsClosure) {
            $this->assertArrayHasKey('subquery', $r[0]);
            $this->assertEquals('havingInSelect', $r[0]['type']);
            $this->assertInstanceOf(SubQuery::class, $r[0]['subquery']);
        } else {
            $this->assertArrayHasKey('value', $r[0]);
            $this->assertEquals('havingIn', $r[0]['type']);
            $this->assertEquals($value, $r[0]['value']);
        }
    }

    private function addHavingTests(
        $aggregate,
        $value,
        $aggregateIsClosureOrExpression = false,
        $valueIsClosureOrExpression = false
    ): void {
        $operator = '=';
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addHaving($aggregate, $value, $operator, $separator);

        $r = $e->getHaving();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('aggregate', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('operator', $r[0]);
        $this->assertArrayHasKey('value', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($operator, $r[0]['operator']);
        $this->assertEquals('havingCondition', $r[0]['type']);

        if ($aggregateIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['aggregate']);
        } else {
            $this->assertEquals($aggregate, $r[0]['aggregate']);
        }

        if ($valueIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value']);
        } else {
            $this->assertEquals($value, $r[0]['value']);
        }
    }

    private function addHavingGroupTests(
        $closure,
        $conditionsCount
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addHavingGroup($closure, $separator);

        $r = $e->getHaving();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('conditions', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);

        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals('havingNested', $r[0]['type']);
        $this->assertIsArray($r[0]['conditions']);
        $this->assertCount($conditionsCount, $r[0]['conditions']);
    }

    private function addWhereTests(
        $column,
        $value,
        $columnIsClosureOrExpression = false,
        $valueIsClosureOrExpression = false
    ): void {
        $operator = '=';
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhere($column, $value, $operator, $separator);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('operator', $r[0]);
        $this->assertArrayHasKey('value', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($operator, $r[0]['operator']);
        $this->assertEquals('whereColumn', $r[0]['type']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['column']);
        } else {
            $this->assertEquals($column, $r[0]['column']);
        }

        if ($valueIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value']);
        } else {
            $this->assertEquals($value, $r[0]['value']);
        }
    }

    private function addWhereInTests(
        $column,
        $value,
        $not,
        $columnIsClosureOrExpression = false,
        $valueIsClosure = false
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereIn($column, $value, $separator, $not);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($not, $r[0]['not']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['column']);
        } else {
            $this->assertEquals($column, $r[0]['column']);
        }

        if ($valueIsClosure) {
            $this->assertArrayHasKey('subquery', $r[0]);
            $this->assertInstanceOf(SubQuery::class, $r[0]['subquery']);
            $this->assertEquals('whereInSelect', $r[0]['type']);
        } else {
            $this->assertArrayHasKey('value', $r[0]);
            $this->assertEquals($value, $r[0]['value']);
            $this->assertEquals('whereIn', $r[0]['type']);
        }
    }

    private function addWhereLikeTests(
        $column,
        $not,
        $columnIsClosureOrExpression = false
    ): void {
        $pattern = '%r';
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereLike($column, $pattern, $separator, $not);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('pattern', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);


        $this->assertEquals($not, $r[0]['not']);
        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($pattern, $r[0]['pattern']);
        $this->assertEquals('whereLike', $r[0]['type']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['column']);
        } else {
            $this->assertEquals($column, $r[0]['column']);
        }
    }

    private function addWhereNullTests(
        $column,
        $not,
        $columnIsClosureOrExpression = false
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereNull($column, $separator, $not);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);


        $this->assertEquals($not, $r[0]['not']);
        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals('whereNull', $r[0]['type']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['column']);
        } else {
            $this->assertEquals($column, $r[0]['column']);
        }
    }

    private function addWhereNopTests(
        $column
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereNop($column, $separator);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals('whereNop', $r[0]['type']);
        $this->assertEquals($column, $r[0]['column']);
    }

    private function addWhereExistsTests(
        $closure,
        $not
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereExists($closure, $separator, $not);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('not', $r[0]);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('subquery', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);

        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($not, $r[0]['not']);
        $this->assertEquals('whereExists', $r[0]['type']);
        $this->assertInstanceOf(SubQuery::class, $r[0]['subquery']);
    }

    private function addJoinClauseTests(
        $table,
        $closure,
        $tableIsClosure,
        $closureIsNull
    ): void {
        $type = 'INNER';

        $e = new QueryStatement();

        $e->addJoinClause($type, $table, $closure);

        $r = $e->getJoins();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('table', $r[0]);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('join', $r[0]);

        $this->assertEquals($type, $r[0]['type']);
        $this->assertIsArray($r[0]['table']);

        if (!$closureIsNull) {
            $this->assertInstanceOf(Join::class, $r[0]['join']);
        } else {
            $this->assertNull($r[0]['join']);
        }

        if ($tableIsClosure) {
            $this->assertInstanceOf(Expression::class, $r[0]['table'][0]);
        }
    }

    private function addWhereBetweenTests(
        $column,
        $value1,
        $value2,
        $not,
        $columnIsClosureOrExpression = false,
        $value1IsClosureOrExpression = false,
        $value2IsClosureOrExpression = false
    ): void {
        $separator = 'AND';

        $e = new QueryStatement();

        $e->addWhereBetween($column, $value1, $value2, $separator, $not);

        $r = $e->getWheres();

        $this->assertCount(1, $r);
        $this->assertArrayHasKey('type', $r[0]);
        $this->assertArrayHasKey('column', $r[0]);
        $this->assertArrayHasKey('separator', $r[0]);
        $this->assertArrayHasKey('not', $r[0]);
        $this->assertArrayHasKey('value1', $r[0]);
        $this->assertArrayHasKey('value2', $r[0]);


        $this->assertEquals($separator, $r[0]['separator']);
        $this->assertEquals($not, $r[0]['not']);
        $this->assertEquals('whereBetween', $r[0]['type']);

        if ($columnIsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['column']);
        } else {
            $this->assertEquals($column, $r[0]['column']);
        }

        if ($value1IsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value1']);
        } else {
            $this->assertEquals($value1, $r[0]['value1']);
        }

        if ($value2IsClosureOrExpression) {
            $this->assertInstanceOf(Expression::class, $r[0]['value2']);
        } else {
            $this->assertEquals($value2, $r[0]['value2']);
        }
    }
}
