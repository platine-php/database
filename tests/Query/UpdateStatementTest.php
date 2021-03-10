<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\UpdateStatement;
use Platine\PlatineTestCase;

/**
 * UpdateStatement class tests
 *
 * @group core
 * @group database
 */
class UpdateStatementTest extends PlatineTestCase
{

    public function testConstructorTableIsStringQueryStatementParamIsNull(): void
    {
        $table = 'foo';
        $e = new UpdateStatement($table);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals([$table], $e->getQueryStatement()->getTables());
    }

    public function testConstructorTableIsArrarQueryStatementParamIsNull(): void
    {
        $table = ['foo'];
        $e = new UpdateStatement($table);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($table, $e->getQueryStatement()->getTables());
    }

    public function testConstructorTableIsStringQueryStatementParamIsNotNull(): void
    {
        $table = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with([$table]);

        $e = new UpdateStatement($table, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testConstructorTableIsArrayQueryStatementParamIsNotNull(): void
    {
        $table = ['foo'];
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with($table);

        $e = new UpdateStatement($table, $qs);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testSet(): void
    {
        $tables = 'foo';

        $sets = [
            'name' => 'foo',
            'status' => false
        ];
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addUpdateColumns')
           ->with($sets);

        $e = new UpdateStatement('foo', $qs);
        $e->set($sets);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }
}
