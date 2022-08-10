<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Closure;
use Platine\Database\Query\Expression;
use Platine\Database\Query\Join;
use Platine\Dev\PlatineTestCase;

/**
 * Join class tests
 *
 * @group core
 * @group database
 */
class JoinTest extends PlatineTestCase
{
    public function testGetJoinConditions(): void
    {
        $e = new Join();
        $this->assertEmpty($e->getJoinConditions());
        $e->on('foo');
        $this->assertCount(1, $e->getJoinConditions());
    }

    public function testOnColumn1IsStringColumn2IsNull(): void
    {
        $column1 = 'foo';
        $column2 = null;
        $separator = 'AND';
        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);

        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsExpressionColumn2IsNull(): void
    {
        $column1 = (new Expression())->column('baz');
        $column2 = null;
        $separator = 'AND';
        $operator = '=';

         $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsStringColumn2IsString(): void
    {
        $column1 = 'foo';
        $column2 = 'bar';
        $separator = 'AND';
        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsStringColumn2IsExpression(): void
    {
        $column1 = 'foo';
        $column2 = (new Expression())->column('baz');
        $separator = 'AND';
        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);

        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsStringColumn2IsClosure(): void
    {
        $column1 = 'foo';
        $column2 = function (Expression $e) {
            $e->column('bazz');
        };
        $separator = 'AND';
        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);

        $this->assertInstanceOf(Expression::class, $conditions[0]['column2']);


        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsStringColumn2IsTrue(): void
    {
        $column1 = 'foo';
        $column2 = true;
        $separator = 'AND';

        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);

        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsClosureColumn2IsTrue(): void
    {
        $column1 = function (Expression $e) {
            $e->column('bazz');
        };
        $column2 = true;
        $separator = 'AND';

        $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();

        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('expression', $conditions[0]);
        $this->assertArrayHasKey('separator', $conditions[0]);

        $this->assertEquals($separator, $conditions[0]['separator']);
        $this->assertInstanceOf(Expression::class, $conditions[0]['expression']);
    }

    public function testOnColumn1IsExpressionColumn2IsTrue(): void
    {
        $column1 = (new Expression())->column('baz');
        $column2 = true;
        $separator = 'AND';

        $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();

        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('expression', $conditions[0]);
        $this->assertArrayHasKey('separator', $conditions[0]);

        $this->assertEquals($separator, $conditions[0]['separator']);
        $this->assertInstanceOf(Expression::class, $conditions[0]['expression']);
    }

    public function testOnColumn1IsClosureColumn2IsNull(): void
    {
        $column1 = function (Join $j) {
            $j->andOn('bazz');
        };
        $column2 = null;
        $separator = 'AND';

        $e = new Join();
        $e->on($column1, $column2);
        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);

        $this->assertArrayHasKey('join', $conditions[0]);

        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals('joinNested', $conditions[0]['type']);
        $this->assertInstanceOf(Join::class, $conditions[0]['join']);
    }

    public function testOnColumn1IsClosureColumn2IsClosure(): void
    {
        $column1 = function (Expression $e) {
            $e->column('bazz');
        };
            $column2 = function (Expression $e) {
                $e->column('bazr');
            };
            $separator = 'AND';
            $operator = '=';

            $e = new Join();
            $e->on($column1, $column2);

            $conditions = $e->getJoinConditions();
            $this->assertCount(1, $conditions);
            $this->assertArrayHasKey('separator', $conditions[0]);
            $this->assertArrayHasKey('type', $conditions[0]);
            $this->assertArrayHasKey('column1', $conditions[0]);
            $this->assertArrayHasKey('column2', $conditions[0]);
            $this->assertArrayHasKey('operator', $conditions[0]);
            $this->assertEquals($separator, $conditions[0]['separator']);

            $this->assertEquals($operator, $conditions[0]['operator']);
            $this->assertInstanceOf(Expression::class, $conditions[0]['column1']);
            $this->assertInstanceOf(Expression::class, $conditions[0]['column2']);
            $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOnColumn1IsClosureColumn2IsString(): void
    {
         $column1 = function (Expression $e) {
            $e->column('bazz');
         };
        $column2 = 'foo';
        $separator = 'AND';
        $operator = '=';

        $e = new Join();
        $e->on($column1, $column2);

        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertInstanceOf(Expression::class, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }

    public function testOrOn(): void
    {
        $column1 = 'foo';
        $column2 = true;
        $separator = 'OR';

        $operator = '=';

        $e = new Join();
        $e->orOn($column1, $column2);

        $conditions = $e->getJoinConditions();
        $this->assertCount(1, $conditions);
        $this->assertArrayHasKey('separator', $conditions[0]);
        $this->assertArrayHasKey('type', $conditions[0]);
        $this->assertArrayHasKey('column1', $conditions[0]);
        $this->assertArrayHasKey('column2', $conditions[0]);
        $this->assertArrayHasKey('operator', $conditions[0]);
        $this->assertEquals($separator, $conditions[0]['separator']);

        $this->assertEquals($operator, $conditions[0]['operator']);
        $this->assertEquals($column1, $conditions[0]['column1']);
        $this->assertEquals($column2, $conditions[0]['column2']);
        $this->assertEquals('joinColumn', $conditions[0]['type']);
    }
}
