<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\BaseStatement;
use Platine\Database\Query\Join;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\Where;
use Platine\Dev\PlatineTestCase;

/**
 * BaseStatement class tests
 *
 * @group core
 * @group database
 */
class BaseStatementTest extends PlatineTestCase
{

    public function testConstructorQueryStatementParamIsNull(): void
    {
        $e = new BaseStatement();
        $wr = $this->getPrivateProtectedAttribute(BaseStatement::class, 'where');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(Where::class, $wr->getValue($e));
    }

    public function testConstructorCustomQueryStatement(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new BaseStatement($qs);
        $wr = $this->getPrivateProtectedAttribute(BaseStatement::class, 'where');

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(Where::class, $wr->getValue($e));
        $this->assertEquals($qs, $e->getQueryStatement());
    }


    public function testJoinTableParamIsString(): void
    {
        $type = 'INNER';
        $table = 'foo';
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('join', $type, $table, $closure);
    }

    public function testJoinTableParamIsArray(): void
    {
        $type = 'INNER';
        $table = ['foo'];
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('join', $type, $table, $closure);
    }

    public function testLeftJoinTableParamIsString(): void
    {
        $type = 'LEFT';
        $table = 'foo';
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('leftJoin', $type, $table, $closure);
    }

    public function testLeftJoinTableParamIsArray(): void
    {
        $type = 'LEFT';
        $table = ['foo'];
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('leftJoin', $type, $table, $closure);
    }

    public function testRightJoinTableParamIsString(): void
    {
        $type = 'RIGHT';
        $table = 'foo';
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('rightJoin', $type, $table, $closure);
    }

    public function testRightJoinTableParamIsArray(): void
    {
        $type = 'RIGHT';
        $table = ['foo'];
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('rightJoin', $type, $table, $closure);
    }

    public function testFullJoinTableParamIsString(): void
    {
        $type = 'FULL';
        $table = 'foo';
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('fullJoin', $type, $table, $closure);
    }

    public function testFullJoinTableParamIsArray(): void
    {
        $type = 'FULL';
        $table = ['foo'];
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('fullJoin', $type, $table, $closure);
    }

    public function testCrossJoinTableParamIsString(): void
    {
        $type = 'CROSS';
        $table = 'foo';
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('crossJoin', $type, $table, $closure);
    }

    public function testCrossJoinTableParamIsArray(): void
    {
        $type = 'CROSS';
        $table = ['foo'];
        $closure = function (Join $j) {
        };

        $this->addJoinClauseTests('crossJoin', $type, $table, $closure);
    }


    private function addJoinClauseTests($method, $type, $table, $closure): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addJoinClause')
           ->with($type, $table, $closure);

        $e = new BaseStatement($qs);
        $e->{$method}($table, $closure);
    }
}
