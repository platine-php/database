<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\QueryStatement;
use Platine\Database\Query\SelectStatement;
use Platine\Database\Query\SubQuery;
use Platine\Dev\PlatineTestCase;

/**
 * SubQuery class tests
 *
 * @group core
 * @group database
 */
class SubQueryTest extends PlatineTestCase
{

    public function testFromString(): void
    {
        $tables = 'foo';

        $e = new SubQuery();

        $o = $e->from($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(SelectStatement::class, $o);
        $this->assertEquals([$tables], $e->getQueryStatement()->getTables());
    }

    public function testFromArray(): void
    {
        $tables = ['foo'];

        $e = new SubQuery();

        $o = $e->from($tables);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(SelectStatement::class, $o);
        $this->assertEquals($tables, $e->getQueryStatement()->getTables());
    }

    public function testClone(): void
    {
        $tables = 'foo';

        $e = new SubQuery();

        $o = $e->from($tables);

        $c = clone $e;

        $sr = $this->getPrivateProtectedAttribute(SubQuery::class, 'select');
        $selectReal = $sr->getValue($e);
        $selectClone = $sr->getValue($c);

        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertInstanceOf(QueryStatement::class, $c->getQueryStatement());
        $this->assertInstanceOf(SelectStatement::class, $o);
        $this->assertEquals($selectClone, $selectReal);
    }
}
