<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use PDO;
use PDOStatement;
use Platine\Database\ResultSet;
use Platine\PlatineTestCase;

/**
 * ResultSet class tests
 *
 * @group core
 * @group database
 */
class ResultSetTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $mocks = [];

        $e = $this->getResultSetInstance($mocks);

        $rst = $this->getPrivateProtectedAttribute(ResultSet::class, 'statement');

        $stmt = $rst->getValue($e);

        $this->assertInstanceOf(PDOStatement::class, $stmt);
    }

    public function testSimpleMethods(): void
    {
        $mocks = [
            'rowCount' => 10,
            'fetch' => 5,
            'closeCursor' => true,
            'fetchColumn' => null,
        ];

        $e = $this->getResultSetInstance($mocks);

        $this->assertEquals(10, $e->count());
        $this->assertEquals(5, $e->next());
        $this->assertTrue($e->flush());
        $this->assertNull($e->column());
    }

    public function testAllCallbackIsNull(): void
    {
        $mocks = [
            'fetchAll' => [],
        ];

        $e = $this->getResultSetInstance($mocks);

        $this->assertEmpty($e->all());
    }

    public function testAllCallbackIsNotNull(): void
    {
        $mocks = [
            'fetchAll' => [],
        ];

        $cb = function () {
        };

        $e = $this->getResultSetInstance($mocks);

        $this->assertEmpty($e->all($cb));
    }


    public function testAllGroupCallbackIsNull(): void
    {
        $mocks = [
            'fetchAll' => [],
        ];

        $e = $this->getResultSetInstance($mocks);

        $this->assertEmpty($e->allGroup());
    }

    public function testAllGroupCallbackIsNotNull(): void
    {
        $mocks = [
            'fetchAll' => [],
        ];

        $cb = function () {
        };

        $e = $this->getResultSetInstance($mocks);

        $this->assertEmpty($e->allGroup(false, $cb));
    }

    public function testGetCallbackIsNotNull(): void
    {
        $mocks = [
            'fetch' => null,
        ];

        $cb = function () {
        };

        $e = $this->getResultSetInstance($mocks);

        $this->assertNull($e->get($cb));
    }

    public function testSetFetchModeClass(): void
    {
        $class = 'MyClass';
        $ctorArgs = [];

        /** @var PDOStatement $st */
        $st = $this->getMockBuilder(PDOStatement::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $st->expects($this->once())
            ->method('setFetchMode')
            ->with(PDO::FETCH_CLASS, $class, $ctorArgs);

        $e = new ResultSet($st);


        $e->fetchClass($class, $ctorArgs);
    }

    public function testSetFetchModeCustomClosure(): void
    {
        $e = $this->getResultSetInstance([]);

        $cb = function (PDOStatement $st) {
            $st->closeCursor();
        };

        $o = $e->fetchCustom($cb);
        $this->assertInstanceOf(ResultSet::class, $o);
    }

    /**
     * @dataProvider setFetchModeDataProvider
     *
     * @param string $method
     * @param type $args
     * @return void
     */
    public function testSetFetchMode(string $method, $args): void
    {
        /** @var PDOStatement $st */
        $st = $this->getMockBuilder(PDOStatement::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $st->expects($this->once())
            ->method('setFetchMode')
            ->with($args);


        $e = new ResultSet($st);

        $e->{$method}();
    }

   /**
     * Data provider for "testSetFetchMode"
     * @return array
     */
    public function setFetchModeDataProvider(): array
    {
        return [
            ['fetchAssoc', PDO::FETCH_ASSOC],
            ['fetchObject', PDO::FETCH_OBJ],
            ['fetchNamed', PDO::FETCH_NAMED],
            ['fetchNum', PDO::FETCH_NUM],
            ['fetchKeyPair', PDO::FETCH_KEY_PAIR]
        ];
    }

    private function getResultSetInstance(array $mockInfos = []): ResultSet
    {
        /** @var PDOStatement $st */
        $st = $this->getMockBuilder(PDOStatement::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $st->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return new ResultSet($st);
    }
}
