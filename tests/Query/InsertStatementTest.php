<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\InsertStatement;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;

/**
 * InsertStatement class tests
 *
 * @group core
 * @group database
 */
class InsertStatementTest extends PlatineTestCase
{
    public function testConstructorQueryStatementParamIsNull(): void
    {
        $e = new InsertStatement();


        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
    }

    public function testConstructorCustomQueryStatement(): void
    {
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new InsertStatement($qs);


        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());

        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testClone(): void
    {
        $e = new InsertStatement();

        $c = clone $e;

        $this->assertInstanceOf(QueryStatement::class, $c->getQueryStatement());
        $this->assertEquals($e->getQueryStatement(), $c->getQueryStatement());
    }



    public function testInsert(): void
    {
        $values = [
            'foo' => 'bar',
            'age' => 1
        ];

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->exactly(2))
           ->method('addColumn');

        $qs->expects($this->exactly(2))
           ->method('addValue');

        $e = new InsertStatement($qs);
        $e->insert($values);
    }

    public function testInto(): void
    {
        $table = 'foo';

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->once())
           ->method('addTables')
           ->with([$table]);

        $e = new InsertStatement($qs);
        $e->into($table);
    }
}
