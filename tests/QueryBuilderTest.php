<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\InsertStatement;
use Platine\Database\Query\Query;
use Platine\Database\Query\UpdateStatement;
use Platine\Database\QueryBuilder;
use Platine\Database\ResultSet;
use Platine\Database\Schema;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * QueryBuilder class tests
 *
 * @group core
 * @group database
 */
class QueryBuilderTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $this->assertInstanceOf(Connection::class, $e->getConnection());
        $this->assertInstanceOf(Schema::class, $e->schema());
    }

    public function testQuery(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $res = $e->query('select * from foo', []);
        $this->assertInstanceOf(ResultSet::class, $res);
    }

    public function testSelect(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $res = $e->from('foo');
        $this->assertInstanceOf(Query::class, $res);
    }

    public function testInsert(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $res = $e->insert(['name' => 1]);
        $this->assertInstanceOf(InsertStatement::class, $res);
    }

    public function testUpdate(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $res = $e->update('foo');
        $this->assertInstanceOf(UpdateStatement::class, $res);
    }

    public function testTransaction(): void
    {
        $cnx = new Connection('SQLite');

        $e = new QueryBuilder($cnx);

        $res = $e->transaction(function ($qb) {
            return 1;
        });
        $this->assertEquals(1, $res);
    }
}
