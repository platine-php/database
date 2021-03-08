<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Query;
use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\Select;
use Platine\Database\ResultSet;
use Platine\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Query class tests
 *
 * @group core
 * @group database
 */
class QueryTest extends PlatineTestCase
{

    public function testConstructorTablesIsString(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $cr = $this->getPrivateProtectedAttribute(Query::class, 'connection');

        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testDistinct(): void
    {
        $this->simpleMethodsTest('distinct', true);
    }

    public function testGroupBy(): void
    {
        $this->simpleMethodsTest('groupBy', 'foo');
    }

    public function testHaving(): void
    {
        $this->simpleMethodsTest('having', 'foo');
        $this->simpleMethodsTest('orHaving', 'foo');
    }

    public function testOrderBy(): void
    {
        $this->simpleMethodsTest('orderBy', 'foo');
    }

    public function testLimitAndOffset(): void
    {
        $this->simpleMethodsTest('limit', 10);
        $this->simpleMethodsTest('offset', 1);
    }

    public function testInto(): void
    {
        $this->simpleMethodsTest('into', 'foo');
    }

    public function testSelect(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->select();

        $this->assertInstanceOf(ResultSet::class, $o);
    }

    public function testDelete(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->delete('foo');

        $this->assertIsInt($o);
    }

    public function testColumn(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->column('foo');

        $this->assertNotEmpty($o);
    }

    public function testCount(): void
    {
        $this->aggregateFunctionsTest('count', 'foo');
    }

    public function testAvg(): void
    {
        $this->aggregateFunctionsTest('avg', 'foo');
    }

    public function testSum(): void
    {
        $this->aggregateFunctionsTest('sum', 'foo');
    }

    public function testMin(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->min('foo');

        $this->assertNotEmpty($o);
    }

    public function testMax(): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->max('foo');

        $this->assertNotEmpty($o);
    }


    private function aggregateFunctionsTest($method, $param): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        $o = $e->{$method}($param);

        $this->assertIsNumeric($o);
    }


    private function simpleMethodsTest($method, $param1 = null): void
    {
        $cnx = new Connection('MySQL');
        $tables = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Query($cnx, $tables, $qs);

        if ($param1 !== null) {
            $o = $e->{$method}($param1);
        } else {
            $o = $e->{$method}();
        }

        $this->assertInstanceOf(Select::class, $o);
    }
}
