<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Expression;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\Where;
use Platine\Database\Query\SubQuery;
use Platine\Database\Query\WhereStatement;
use Platine\Dev\PlatineTestCase;

/**
 * WhereStatement class tests
 *
 * @group core
 * @group database
 */
class WhereStatementTest extends PlatineTestCase
{
    public function testConstructorQueryStatementParamIsNull(): void
    {
        $e = new WhereStatement();
        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(Where::class, $wr->getValue($e));
    }

    public function testConstructorCustomQueryStatement(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new WhereStatement($qs);
        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(Where::class, $wr->getValue($e));
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testClone(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();


        $e = new WhereStatement($qs);

        $c = clone $e;
        $qsr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'queryStatement');
        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $this->assertInstanceOf(QueryStatement::class, $qsr->getValue($c));
        $this->assertInstanceOf(Where::class, $wr->getValue($c));
    }


    public function testWhereColumnParamIsStringAndExpressionIsFalse(): void
    {
        $column = 'foo';
        $isExpr = false;
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $e = new WhereStatement($qs);
        $e->where($column, $isExpr);

        $where = $wr->getValue($e);
        $this->assertInstanceOf(Where::class, $where);

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

        $this->assertEquals($column, $cr->getValue($where));
        $this->assertEquals($separator, $sr->getValue($where));
    }

    public function testWhereColumnParamIsExpressionAndExpressionIsFalse(): void
    {
        $column = new Expression();
        $column->column('bar');
        $isExpr = false;
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $e = new WhereStatement($qs);
        $e->where($column, $isExpr);

        $where = $wr->getValue($e);
        $this->assertInstanceOf(Where::class, $where);

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

        $this->assertEquals($separator, $sr->getValue($where));
        $ex = $cr->getValue($where);
        $this->assertInstanceOf(Expression::class, $ex);
        $expressions = $ex->getExpressions();
        $this->assertCount(1, $expressions);
        $this->assertArrayHasKey('type', $expressions[0]);
        $this->assertArrayHasKey('value', $expressions[0]);
        $this->assertEquals('bar', $expressions[0]['value']);
        $this->assertEquals('column', $expressions[0]['type']);
    }

    public function testWhereColumnParamIsClosureAndExpressionIsFalse(): void
    {
        $column = function (Expression $exp) {
            $exp->column('baz');
        };

        $isExpr = false;
        $separator = 'AND';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereGroup')
           ->with($column, $separator);

        $e = new WhereStatement($qs);
        $e->where($column, $isExpr);
    }

    public function testOrWhereColumnParamIsStringAndExpressionIsFalse(): void
    {
        $column = 'foo';
        $isExpr = false;
        $separator = 'OR';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $wr = $this->getPrivateProtectedAttribute(WhereStatement::class, 'where');

        $e = new WhereStatement($qs);
        $e->orWhere($column, $isExpr);

        $where = $wr->getValue($e);
        $this->assertInstanceOf(Where::class, $where);

        $sr = $this->getPrivateProtectedAttribute(Where::class, 'separator');
        $cr = $this->getPrivateProtectedAttribute(Where::class, 'column');

        $this->assertEquals($column, $cr->getValue($where));
        $this->assertEquals($separator, $sr->getValue($where));
    }

    public function testWhereExists(): void
    {

        $separator = 'AND';
        $not = false;

        $this->addWhereExistsTests('whereExists', $separator, $not);
    }

    public function testOrWhereExists(): void
    {

        $separator = 'OR';
        $not = false;

        $this->addWhereExistsTests('orWhereExists', $separator, $not);
    }

    public function testWhereNotExists(): void
    {

        $separator = 'AND';
        $not = true;

        $this->addWhereExistsTests('whereNotExists', $separator, $not);
    }

    public function testOrWhereNotExists(): void
    {

        $separator = 'OR';
        $not = true;

        $this->addWhereExistsTests('OrWhereNotExists', $separator, $not);
    }

    private function addWhereExistsTests($method, $separator, $not): void
    {
        $select = function (SubQuery $sq) {
            $sq->from('bar')->select('baz');
        };

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addWhereExists')
           ->with($select, $separator, $not);

        $e = new WhereStatement($qs);
        $e->{$method}($select);
    }
}
