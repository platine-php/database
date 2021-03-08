<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Closure;
use Platine\Database\Query\Expression;
use Platine\Database\Query\Having;
use Platine\Database\Query\HavingExpression;
use Platine\Database\Query\QueryStatement;
use Platine\PlatineTestCase;

/**
 * HavingExpression class tests
 *
 * @group core
 * @group database
 */
class HavingExpressionTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new HavingExpression($qs);
        $qsr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'queryStatement');
        $hr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'having');
        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($e));
        $this->assertInstanceOf(Having::class, $hr->getValue($e));
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new HavingExpression($qs);
        $e->init((new Expression())->column('foo'), 'AND');

        $c = clone $e;
        $qsr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'queryStatement');
        $hr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'having');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($c));
        $this->assertInstanceOf(Having::class, $hr->getValue($c));
    }

    public function testInitColumnParamIsString(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                   ->getMock();

        $e = new HavingExpression($qs);
        $e->init('foo', 'AND');

        $sr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'column');

        $this->assertEquals('foo', $cr->getValue($e));
        $this->assertEquals('AND', $sr->getValue($e));
    }

    public function testInitColumnParamIsExpression(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new HavingExpression($qs);
        $exp = new Expression();
        $exp->column('bar');
        $e->init($exp, 'AND');

        $sr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'column');

        $this->assertEquals('AND', $sr->getValue($e));
        $ex = $cr->getValue($e);
        $this->assertInstanceOf(Expression::class, $ex);
        $expressions = $ex->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('bar', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testInitColumnParamIsClosure(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                   ->getMock();

        $e = new HavingExpression($qs);

        $e->init(function (Expression $exp) {
            $exp->column('baz');
        }, 'AND');

        $sr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'column');

        $this->assertEquals('AND', $sr->getValue($e));
        $ex = $cr->getValue($e);
        $this->assertInstanceOf(Expression::class, $ex);
        $expressions = $ex->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('baz', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testCountAggregateIsStringWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateStringFunctionTests('count', $distinct);
    }

    public function testCountAggregateIsStringWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateStringFunctionTests('count', $distinct);
    }

    public function testCountAggregateIsExpressionWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateExpressionFunctionTests('count', $distinct);
    }

    public function testCountAggregateIsExpressionWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateExpressionFunctionTests('count', $distinct);
    }

    public function testCountAggregateIsClosureWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateClosureFunctionTests('count', $distinct);
    }

    public function testCountAggregateIsClosureWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateClosureFunctionTests('count', $distinct);
    }

    public function testAvgAggregateIsStringWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateStringFunctionTests('avg', $distinct);
    }

    public function testAvgAggregateIsStringWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateStringFunctionTests('avg', $distinct);
    }

    public function testAvgAggregateIsExpressionWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateExpressionFunctionTests('avg', $distinct);
    }

    public function testAvgAggregateIsExpressionWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateExpressionFunctionTests('avg', $distinct);
    }

    public function testAvgAggregateIsClosureWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateClosureFunctionTests('avg', $distinct);
    }

    public function testAvgAggregateIsClosureWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateClosureFunctionTests('avg', $distinct);
    }

    public function testMinAggregateIsStringWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateStringFunctionTests('min', $distinct);
    }

    public function testMinAggregateIsStringWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateStringFunctionTests('min', $distinct);
    }

    public function testMinAggregateIsExpressionWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateExpressionFunctionTests('min', $distinct);
    }

    public function testMinAggregateIsExpressionWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateExpressionFunctionTests('min', $distinct);
    }

    public function testMinAggregateIsClosureWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateClosureFunctionTests('min', $distinct);
    }

    public function testMinAggregateIsClosureWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateClosureFunctionTests('min', $distinct);
    }

    public function testMaxAggregateIsStringWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateStringFunctionTests('max', $distinct);
    }

    public function testMaxAggregateIsStringWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateStringFunctionTests('max', $distinct);
    }

    public function testMaxAggregateIsExpressionWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateExpressionFunctionTests('max', $distinct);
    }

    public function testMaxAggregateIsExpressionWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateExpressionFunctionTests('max', $distinct);
    }

    public function testMaxAggregateIsClosureWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateClosureFunctionTests('max', $distinct);
    }

    public function testMaxAggregateIsClosureWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateClosureFunctionTests('max', $distinct);
    }

    public function testSumAggregateIsStringWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateStringFunctionTests('sum', $distinct);
    }

    public function testSumAggregateIsStringWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateStringFunctionTests('sum', $distinct);
    }

    public function testSumAggregateIsExpressionWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateExpressionFunctionTests('sum', $distinct);
    }

    public function testSumAggregateIsExpressionWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateExpressionFunctionTests('sum', $distinct);
    }

    public function testSumAggregateIsClosureWithDistinct(): void
    {
        $distinct = true;

        $this->aggregateClosureFunctionTests('sum', $distinct);
    }

    public function testSumAggregateIsClosureWithoutDistinct(): void
    {
        $distinct = false;

        $this->aggregateClosureFunctionTests('sum', $distinct);
    }

    /**
     *
     * @param string $method
     * @param bool $distinct
     * @return void
     */
    private function aggregateStringFunctionTests(string $method, bool $distinct): void
    {

        $aggregate = 'foo';

        $this->aggregateFunctionTests($method, $aggregate, $distinct);
    }

    /**
     *
     * @param string $method
     * @param bool $distinct
     * @return void
     */
    private function aggregateExpressionFunctionTests(string $method, bool $distinct): void
    {

        $aggregate = (new Expression())->column('foo');

        $this->aggregateFunctionTests($method, $aggregate, $distinct);
    }

    private function aggregateClosureFunctionTests(string $method, bool $distinct): void
    {

        $aggregate = function () {
        };

        $this->aggregateFunctionTests($method, $aggregate, $distinct);
    }

    /**
     *
     * @param string $method
     * @param string|Closure|Expression $aggregate
     * @param bool $distinct
     * @return void
     */
    private function aggregateFunctionTests(string $method, $aggregate, bool $distinct): void
    {
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new HavingExpression($qs);
        $e->init($aggregate, $separator);
        $e->{$method}($distinct);

        $hr = $this->getPrivateProtectedAttribute(HavingExpression::class, 'having');
        $having = $hr->getValue($e);

        $sr = $this->getPrivateProtectedAttribute(Having::class, 'separator');
        $ar = $this->getPrivateProtectedAttribute(Having::class, 'aggregate');

        $this->assertInstanceOf(Expression::class, $ar->getValue($having));
        $this->assertEquals($separator, $sr->getValue($having));
    }
}
