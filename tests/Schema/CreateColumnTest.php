<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Database\Schema\BaseColumn;
use Platine\PlatineTestCase;

/**
 * CreateColumn class tests
 *
 * @group core
 * @group database
 */
class CreateColumnTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $ctMockMethods = $this->getClassMethodsToMock(
            CreateTable::class,
            []
        );

        /** @var CreateTable $ct */
        $ct = $this->getMockBuilder(CreateTable::class)
                    ->onlyMethods($ctMockMethods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $name = 'foo';
        $type = null;
        $e = new CreateColumn($ct, $name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertNull($e->getType());
        $this->assertInstanceOf(CreateTable::class, $e->getTable());
        $this->assertEquals($ct, $e->getTable());

        $name = 'foo';
        $type = 'int';
        $e = new CreateColumn($ct, $name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertEquals($type, $e->getType());
        $this->assertEquals($ct, $e->getTable());
        $this->assertInstanceOf(CreateTable::class, $e->getTable());
    }


    public function testAutoincrement(): void
    {
        $this->callToCreateTableMethods('autoincrement', null, true, false);
        $this->callToCreateTableMethods('autoincrement', 'foo_name', true, false);
    }

    public function testPrimary(): void
    {
        $this->callToCreateTableMethods('primary', null, false, true);
        $this->callToCreateTableMethods('primary', 'foo_column', false, true);
    }

    public function testUnique(): void
    {
        $this->callToCreateTableMethods('unique', null, false, true);
        $this->callToCreateTableMethods('unique', 'foo_column', false, true);
    }

    public function testIndex(): void
    {
        $this->callToCreateTableMethods('index', null, false, true);
        $this->callToCreateTableMethods('index', 'foo_column', false, true);
    }

    private function callToCreateTableMethods(
        string $method,
        ?string $name,
        $useInstance = false,
        $useName = false
    ): void {
        $ctMockMethods = $this->getClassMethodsToMock(
            CreateTable::class,
            []
        );

        /** @var CreateTable $ct */
        $ct = $this->getMockBuilder(CreateTable::class)
                    ->onlyMethods($ctMockMethods)
                    ->disableOriginalConstructor()
                    ->getMock();



        $column = 'foo';

        $e = new CreateColumn($ct, $column);

        $ct = $ct->expects($this->once())
            ->method($method);

        if ($useInstance) {
            $ct = $ct->with($e, $name);
        } elseif ($useName) {
            $ct = $ct->with($column, $name);
        }


        $e->{$method}($name);
    }
}
