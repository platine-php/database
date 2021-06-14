<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Update;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Update class tests
 *
 * @group core
 * @group database
 */
class UpdateTest extends PlatineTestCase
{

    public function testConstructorTableIsString(): void
    {
        $cnx = new Connection('MySQL');
        $table = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Update($cnx, $table, $qs);

        $cr = $this->getPrivateProtectedAttribute(Update::class, 'connection');

        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testSet(): void
    {
        $cnx = new Connection('MySQL');
        $table = 'foo';
        $sets = [
            'name' => 'foo',
            'status' => false
        ];

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'addTables',
            'addUpdateColumns',
            'getTables',
            'getColumns',
            'closureToExpression',
        ]);


        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();


        $e = new Update($cnx, $table, $qs);

        $e->set($sets);

        $expected = "UPDATE `foo` SET `name` = 'foo', `status` = FALSE";
        $this->assertEquals($expected, $cnx->getRawSql());
    }

    public function testIncrementDecrementUsingArrayOfColumns(): void
    {
        $name = ['bar_increment' => 4];
        $value = 4;

        $this->incrementDecrementUsingArrayOfColumnsTests('increment', $name, '+', $value);
        $this->incrementDecrementUsingArrayOfColumnsTests('decrement', $name, '-', $value);
    }

    public function testIncrementDecrementSimple(): void
    {
        $this->incrementDecrementSimple('increment', '+', 1);
        $this->incrementDecrementSimple('increment', '+', 3);
        $this->incrementDecrementSimple('decrement', '-', 1);
        $this->incrementDecrementSimple('decrement', '-', 3);
    }

    private function incrementDecrementUsingArrayOfColumnsTests($method, $name, $sign, $value): void
    {
        $cnx = new Connection('MySQL');
        $table = 'foo';

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'addTables',
            'addUpdateColumns',
            'getTables',
            'getColumns',
            'addColumn',
            'closureToExpression',
        ]);


        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();


        $e = new Update($cnx, $table, $qs);

        $e->{$method}($name);

        $expected = sprintf("UPDATE `foo` SET `bar_increment` = `bar_increment` %s %s", $sign, $value);
        $this->assertEquals($expected, $cnx->getRawSql());
    }

    private function incrementDecrementSimple($method, $sign, $value = 1): void
    {
        $cnx = new Connection('MySQL');
        $table = 'foo';
        $name = 'bar_increment';

        $qsMockMethods = $this->getClassMethodsToMock(QueryStatement::class, [
            'addTables',
            'addUpdateColumns',
            'getTables',
            'getColumns',
            'addColumn',
            'closureToExpression',
        ]);


        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->onlyMethods($qsMockMethods)
                    ->getMock();


        $e = new Update($cnx, $table, $qs);

        $e->{$method}($name, $value);

        $expected = sprintf("UPDATE `foo` SET `bar_increment` = `bar_increment` %s %s", $sign, $value);
        $this->assertEquals($expected, $cnx->getRawSql());
    }
}
