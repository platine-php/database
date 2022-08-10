<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\Having;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;

/**
 * Having class tests
 *
 * @group core
 * @group database
 */
class HavingTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Having($qs);
        $qsr = $this->getPrivateProtectedAttribute(Having::class, 'queryStatement');
        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($e));
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new Having($qs);
        $exp = new Expression();
        $exp->column('bar');
        $e->init($exp, 'AND');

        $c = clone $e;
        $qsr = $this->getPrivateProtectedAttribute(Having::class, 'queryStatement');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($c));
    }

    public function testInitAggregateParamIsString(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new Having($qs);
        $e->init('foo', 'AND');

        $sr = $this->getPrivateProtectedAttribute(Having::class, 'separator');
        $ar = $this->getPrivateProtectedAttribute(Having::class, 'aggregate');

        $this->assertEquals('foo', $ar->getValue($e));
        $this->assertEquals('AND', $sr->getValue($e));
    }


    public function testInitAggregateParamIsExpression(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new Having($qs);
        $exp = new Expression();
        $exp->column('bar');
        $e->init($exp, 'AND');

        $sr = $this->getPrivateProtectedAttribute(Having::class, 'separator');
        $ar = $this->getPrivateProtectedAttribute(Having::class, 'aggregate');

        $this->assertEquals('AND', $sr->getValue($e));
        $ex = $ar->getValue($e);
        $this->assertInstanceOf(Expression::class, $ex);
        $expressions = $ex->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('bar', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testInitAggregateParamIsClosure(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new Having($qs);
        $e->init(function (Expression $exp) {
            $exp->column('baz');
        }, 'AND');

        $sr = $this->getPrivateProtectedAttribute(Having::class, 'separator');
        $ar = $this->getPrivateProtectedAttribute(Having::class, 'aggregate');

        $this->assertEquals('AND', $sr->getValue($e));
        $ex = $ar->getValue($e);
        $this->assertInstanceOf(Expression::class, $ex);
        $expressions = $ex->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('baz', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testIs(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '=';

        $this->comparaisonTests('is', $aggregate, $value, $operator);
    }

    public function testEq(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '=';

        $this->comparaisonTests('eq', $aggregate, $value, $operator);
    }

    public function testIsNot(): void
    {
        $aggregate = 'foo';
        $value = 456;
        $operator = '!=';

        $this->comparaisonTests('isNot', $aggregate, $value, $operator);
    }

    public function testIsNotValueIsStringAndIsColumn(): void
    {
        $aggregate = 'foo';
        $value = 'baz';
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHaving');

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->isNot($value, true);
    }

    public function testNeq(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '!=';

        $this->comparaisonTests('neq', $aggregate, $value, $operator);
    }

    public function testLt(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '<';

        $this->comparaisonTests('lt', $aggregate, $value, $operator);
    }

    public function testGt(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '>';

        $this->comparaisonTests('gt', $aggregate, $value, $operator);
    }

    public function testLte(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '<=';

        $this->comparaisonTests('lte', $aggregate, $value, $operator);
    }

    public function testGte(): void
    {
        $aggregate = 'foo';
        $value = 1;
        $operator = '>=';

        $this->comparaisonTests('gte', $aggregate, $value, $operator);
    }

    public function testBetween(): void
    {
        $aggregate = 'foo';
        $value1 = 1;
        $value2 = 3;
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHavingBetween')
           ->with($aggregate, $value1, $value2, $separator, $not);

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->between($value1, $value2);
    }

    public function testNotBetween(): void
    {
        $aggregate = 'foo';
        $value1 = 1;
        $value2 = 3;
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addHavingBetween')
           ->with($aggregate, $value1, $value2, $separator, $not);

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->notBetween($value1, $value2);
    }

    public function testIn(): void
    {
        $aggregate = 'foo';
        $value = [1, 2, 3];
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $qs->expects($this->once())
           ->method('addHavingIn')
           ->with($aggregate, $value, $separator, $not);

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->in($value);
    }

    public function testNotIn(): void
    {
        $aggregate = 'foo';
        $value = [1, 2, 3];
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $qs->expects($this->once())
           ->method('addHavingIn')
           ->with($aggregate, $value, $separator, $not);

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->notIn($value);
    }

    private function comparaisonTests($method, $aggregate, $value, $operator): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $separator = 'AND';

        $qs->expects($this->once())
           ->method('addHaving')
           ->with($aggregate, $value, $operator, $separator);

        $e = new Having($qs);
        $e->init($aggregate, $separator);
        $e->{$method}($value, true);
    }
}
