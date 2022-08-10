<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\SelectStatement;
use Platine\Database\Query\SubQuery;
use Platine\Dev\PlatineTestCase;

/**
 * Expression class tests
 *
 * @group core
 * @group database
 */
class ExpressionTest extends PlatineTestCase
{
    public function testGetExpressions(): void
    {
        $e = new Expression();
        $this->assertEmpty($e->getExpressions());
        $e->column('foo');
        $this->assertCount(1, $e->getExpressions());
    }

    public function testColumn(): void
    {
        $e = new Expression();
        $e->column('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('foo', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testOperator(): void
    {
        $e = new Expression();
        $e->op('+');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('+', $expressions[0]['value']);
        $this->assertEquals('op', $expressions[0]['type']);
    }

    public function testValue(): void
    {
        $e = new Expression();
        $e->value(1);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals(1, $expressions[0]['value']);
        $this->assertEquals('value', $expressions[0]['type']);
    }

    public function testGroup(): void
    {
        $e = new Expression();
        $e->group(function (Expression $exp) {
            $exp->column('bar');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);

        /** @var Expression $expr */
        $expr = $expressions[0]['value']->getExpressions();
        $this->assertEquals('group', $expressions[0]['type']);
        $this->assertEquals('bar', $expr[0]['value']);
    }

    public function testCountParamIsStringWithoutDistinct(): void
    {
        $e = new Expression();
        $e->count('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('COUNT', $func['name']);
        $this->assertCount(1, $func['column']);
        $this->assertEquals('foo', $func['column'][0]);
        $this->assertFalse($func['distinct']);
    }

    public function testCountParamIsStringWithDistinct(): void
    {
        $e = new Expression();
        $e->count('foo', true);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('COUNT', $func['name']);
        $this->assertCount(1, $func['column']);
        $this->assertEquals('foo', $func['column'][0]);
        $this->assertTrue($func['distinct']);
    }

    public function testCountParamIsArrayImpliciteDistinct(): void
    {
        $e = new Expression();
        $e->count(['foo', 'bar']);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('COUNT', $func['name']);
        $this->assertCount(2, $func['column']);
        $this->assertEquals('foo', $func['column'][0]);
        $this->assertEquals('bar', $func['column'][1]);
        $this->assertTrue($func['distinct']);
    }

    public function testCountParamIsExpression(): void
    {
        $e = new Expression();
        $oExpr = (new Expression())->column('baz');

        $e->count($oExpr);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('COUNT', $func['name']);
        $this->assertCount(1, $func['column']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column'][0]);

        $vExpr = $func['column'][0]->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testCountParamIsClosure(): void
    {
        $e = new Expression();

        $e->count(function (Expression $oExpr) {
            $oExpr->column('baz');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('COUNT', $func['name']);
        $this->assertCount(1, $func['column']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column'][0]);

        $vExpr = $func['column'][0]->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testSumParamIsStringWithoutDistinct(): void
    {
        $e = new Expression();
        $e->sum('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('SUM', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertFalse($func['distinct']);
    }

    public function testSumParamIsStringWithDistinct(): void
    {
        $e = new Expression();
        $e->sum('foo', true);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('SUM', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertTrue($func['distinct']);
    }

    public function testSumParamIsExpression(): void
    {
        $e = new Expression();
        $oExpr = (new Expression())->column('baz');

        $e->sum($oExpr);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('SUM', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testSumParamIsClosure(): void
    {
        $e = new Expression();

        $e->sum(function (Expression $oExpr) {
            $oExpr->column('baz');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('SUM', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testAvgParamIsStringWithoutDistinct(): void
    {
        $e = new Expression();
        $e->avg('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('AVG', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertFalse($func['distinct']);
    }

    public function testAvgParamIsStringWithDistinct(): void
    {
        $e = new Expression();
        $e->avg('foo', true);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('AVG', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertTrue($func['distinct']);
    }

    public function testAvgParamIsExpression(): void
    {
        $e = new Expression();
        $oExpr = (new Expression())->column('baz');

        $e->avg($oExpr);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('AVG', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testAvgParamIsClosure(): void
    {
        $e = new Expression();

        $e->avg(function (Expression $oExpr) {
            $oExpr->column('baz');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('AVG', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testMinParamIsStringWithoutDistinct(): void
    {
        $e = new Expression();
        $e->min('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MIN', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertFalse($func['distinct']);
    }

    public function testMinParamIsStringWithDistinct(): void
    {
        $e = new Expression();
        $e->min('foo', true);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MIN', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertTrue($func['distinct']);
    }

    public function testMinParamIsExpression(): void
    {
        $e = new Expression();
        $oExpr = (new Expression())->column('baz');

        $e->min($oExpr);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MIN', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testMinParamIsClosure(): void
    {
        $e = new Expression();

        $e->min(function (Expression $oExpr) {
            $oExpr->column('baz');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MIN', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testMaxParamIsStringWithoutDistinct(): void
    {
        $e = new Expression();
        $e->max('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MAX', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertFalse($func['distinct']);
    }

    public function testMaxParamIsStringWithDistinct(): void
    {
        $e = new Expression();
        $e->max('foo', true);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MAX', $func['name']);
        $this->assertEquals('foo', $func['column']);
        $this->assertTrue($func['distinct']);
    }

    public function testMaxParamIsExpression(): void
    {
        $e = new Expression();
        $oExpr = (new Expression())->column('baz');

        $e->max($oExpr);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MAX', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testMaxParamIsClosure(): void
    {
        $e = new Expression();

        $e->max(function (Expression $oExpr) {
            $oExpr->column('baz');
        });
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('function', $expressions[0]['type']);

        $func = $expressions[0]['value'];
        $this->assertCount(4, $func);
        $this->assertArrayHasKey('type', $func);
        $this->assertArrayHasKey('name', $func);
        $this->assertArrayHasKey('column', $func);
        $this->assertArrayHasKey('distinct', $func);
        $this->assertEquals('aggregateFunction', $func['type']);
        $this->assertEquals('MAX', $func['name']);
        $this->assertFalse($func['distinct']);
        $this->assertInstanceOf(Expression::class, $func['column']);

        $vExpr = $func['column']->getExpressions();
        $this->assertArrayHasKey('type', $vExpr[0]);
        $this->assertArrayHasKey('value', $vExpr[0]);
        $this->assertEquals('baz', $vExpr[0]['value']);
        $this->assertEquals('column', $vExpr[0]['type']);
    }

    public function testFrom(): void
    {
        // Param is string
        $e = new Expression();

        /** @var SelectStatement $sq */
        $ss = $e->from('foo');
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('subquery', $expressions[0]['type']);

        $this->assertInstanceOf(SubQuery::class, $expressions[0]['value']);
        $this->assertInstanceOf(SelectStatement::class, $ss);

        // Param is array
        $e = new Expression();

        /** @var SelectStatement $sq */
        $ss = $e->from(['foo', 'baz']);
        $expressions = $e->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('subquery', $expressions[0]['type']);

        $this->assertInstanceOf(SubQuery::class, $expressions[0]['value']);
        $this->assertInstanceOf(SelectStatement::class, $ss);
    }
}
