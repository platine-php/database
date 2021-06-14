<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Query\Insert;
use Platine\Database\Query\QueryStatement;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;

/**
 * Insert class tests
 *
 * @group core
 * @group database
 */
class InsertTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $cnx = new Connection('MySQL');

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $e = new Insert($cnx, $qs);

        $cr = $this->getPrivateProtectedAttribute(Insert::class, 'connection');

        $this->assertInstanceOf(Connection::class, $cr->getValue($e));
        $this->assertInstanceOf(QueryStatement::class, $e->getQueryStatement());
        $this->assertEquals($qs, $e->getQueryStatement());
    }

    public function testInto(): void
    {
        $cnx = new Connection('MySQL');

        $table = 'foo';

        $columns = [
          [
              'name' => 'foo',
              'alias' => null
          ],
          [
              'name' => 'age',
              'alias' => null
          ]
        ];

        $values = [
            'foo' => 'bar',
            'age' => 1
        ];

        /** @var QueryStatement $qs */
        $qs = $this->getMockBuilder(QueryStatement::class)
                    ->getMock();

        $qs->expects($this->any())
            ->method('getColumns')
            ->will($this->returnValue($columns));

        $qs->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue(array_values($values)));

        $qs->expects($this->any())
            ->method('getTables')
            ->will($this->returnValue([$table]));


        $e = new Insert($cnx, $qs);


        $e->insert($values)->into($table);

        $expected = "INSERT INTO `foo`(`foo`, `age`) VALUES ('bar', 1)";
        $this->assertEquals($expected, $cnx->getRawSql());
    }
}
