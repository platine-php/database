<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\DeleteStatement;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;

/**
 * DeleteStatement class tests
 *
 * @group core
 * @group database
 */
class DeleteStatementTest extends PlatineTestCase
{
    public function testConstructorFromIsStringQueryStatementParamIsNull(): void
    {
        $from = 'foo';
        $e = new DeleteStatement($from);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals([$from], $e->getQueryStatement()->getFrom());
    }

    public function testConstructorFromIsArrarQueryStatementParamIsNull(): void
    {
        $from = ['foo'];
        $e = new DeleteStatement($from);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($from, $e->getQueryStatement()->getFrom());
    }

    public function testConstructorFromIsStringQueryStatementParamIsNotNull(): void
    {
        $from = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('setFrom')
           ->with([$from]);

        $e = new DeleteStatement($from, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testConstructorFromIsArrayQueryStatementParamIsNotNull(): void
    {
        $from = ['foo'];
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('setFrom')
           ->with($from);

        $e = new DeleteStatement($from, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testDeleteTablesParamIsString(): void
    {
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with([$tables]);

        $e = new DeleteStatement('foo', $qs);
        $e->delete($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testDeleteTablesParamIsArray(): void
    {
        $tables = ['foo'];
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with($tables);

        $e = new DeleteStatement(['from'], $qs);
        $e->delete($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }
}
