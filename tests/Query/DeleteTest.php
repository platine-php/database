<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Delete;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Delete class tests
 *
 * @group core
 * @group database
 */
class DeleteTest extends PlatineTestCase
{
    public function testConstructorFromIsString(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Delete($cnx, $from, $qs);

        $cr = $this->getPrivateProtectedAttribute(Delete::class, 'connection');

        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testDelete(): void
    {
        $cnx = new Connection('MySQL');
        $from = 'foo';
        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->any())
                ->method('getFrom')
                ->will($this->returnValue([$from]));

        $e = new Delete($cnx, $from, $qs);

        $e->delete($from);

        $expected = 'DELETE FROM `foo`';
        $this->assertEquals($expected, $cnx->getRawSql());
    }
}
