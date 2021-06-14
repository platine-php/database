<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\Where;
use Platine\Database\Query\WhereStatement;
use Platine\Dev\PlatineTestCase;

/**
 * Where class tests
 *
 * @group core
 * @group database
 */
class WhereTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $e = new Where($ws, $qs);
        $qsr = $this->getPrivateProtectedAttribute(Where::class, 'queryStatement');
        $wsr = $this->getPrivateProtectedAttribute(Where::class, 'whereStatement');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($e));
        $this->assertInstanceOf(WhereStatement::class, $wsr->getValue($e));
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $e = new Where($ws, $qs);
        $exp = new Expression();
        $exp->column('bar');
        $e->init($exp, 'AND');

        $c = clone $e;
        $qsr = $this->getPrivateProtectedAttribute(Where::class, 'queryStatement');
        $wsr = $this->getPrivateProtectedAttribute(Where::class, 'whereStatement');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($c));
        $this->assertInstanceOf(WhereStatement::class, $wsr->getValue($c));
    }

    public function testInitColumnParamIsString(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $e = new Where($ws, $qs);
        $e->init('foo', 'AND');

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

        $this->assertEquals('foo', $cr->getValue($e));
        $this->assertEquals('AND', $sr->getValue($e));
    }

    public function testInitColumnParamIsExpression(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $e = new Where($ws, $qs);
        $exp = new Expression();
        $exp->column('bar');
        $e->init($exp, 'AND');

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

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

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $e = new Where($ws, $qs);
        $e->init(function (Expression $exp) {
            $exp->column('baz');
        }, 'AND');

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

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

    public function testIs(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '=';

        $this->comparaisonTests('is', $column, $value, $operator);
    }

    public function testEq(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '=';

        $this->comparaisonTests('eq', $column, $value, $operator);
    }

    public function testIsNot(): void
    {
        $column = 'foo';
        $value = 456;
        $operator = '!=';

        $this->comparaisonTests('isNot', $column, $value, $operator);
    }

    public function testIsNotValueIsStringAndIsColumn(): void
    {
        $column = 'foo';
        $value = 'baz';
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();




        $qs->expects($this->once())
           ->method('addWhere');

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->isNot($value, true);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testNeq(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '!=';

        $this->comparaisonTests('neq', $column, $value, $operator);
    }

    public function testLt(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '<';

        $this->comparaisonTests('lt', $column, $value, $operator);
    }

    public function testGt(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '>';

        $this->comparaisonTests('gt', $column, $value, $operator);
    }

    public function testLte(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '<=';

        $this->comparaisonTests('lte', $column, $value, $operator);
    }

    public function testGte(): void
    {
        $column = 'foo';
        $value = 1;
        $operator = '>=';

        $this->comparaisonTests('gte', $column, $value, $operator);
    }

    public function testBetween(): void
    {
        $column = 'foo';
        $value1 = 1;
        $value2 = 3;
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereBetween')
           ->with($column, $value1, $value2, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->between($value1, $value2);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testNotBetween(): void
    {
        $column = 'foo';
        $value1 = 1;
        $value2 = 3;
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereBetween')
           ->with($column, $value1, $value2, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->notBetween($value1, $value2);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testNotLike(): void
    {
        $column = 'foo';
        $value = '%bar%';
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereLike')
           ->with($column, $value, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->notLike($value);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testLike(): void
    {
        $column = 'foo';
        $value = '%bar%';
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereLike')
           ->with($column, $value, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->like($value);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testIn(): void
    {
        $column = 'foo';
        $value = [1, 2, 3];
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereIn')
           ->with($column, $value, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->in($value);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testNotIn(): void
    {
        $column = 'foo';
        $value = [1, 2, 3];
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereIn')
           ->with($column, $value, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->notIn($value);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testIsNotNull(): void
    {
        $column = 'foo';
        $separator = 'AND';
        $not = true;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereNull')
           ->with($column, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->isNotNull();
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testIsNull(): void
    {
        $column = 'foo';
        $separator = 'AND';
        $not = false;

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereNull')
           ->with($column, $separator, $not);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->isNull();
        $this->assertInstanceOf(WhereStatement::class, $o);
    }

    public function testNop(): void
    {
        $column = 'foo';
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereNop')
           ->with($column, $separator);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->nop();
        $this->assertInstanceOf(WhereStatement::class, $o);
    }


    private function comparaisonTests($method, $column, $value, $operator): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        /** @var WhereStatement $ws */
        $ws = $this->getMockBuilder(WhereStatement::class)
                    ->getMock();


        $separator = 'AND';

        $qs->expects($this->once())
           ->method('addWhere')
           ->with($column, $value, $operator, $separator);

        $e = new Where($ws, $qs);
        $e->init($column, $separator);
        $o = $e->{$method}($value, true);
        $this->assertInstanceOf(WhereStatement::class, $o);
    }
}
