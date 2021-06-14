<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\BaseColumn;
use Platine\Dev\PlatineTestCase;

/**
 * AlterColumn class tests
 *
 * @group core
 * @group database
 */
class AlterColumnTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $atMockMethods = $this->getClassMethodsToMock(
            AlterTable::class,
            []
        );

        /** @var AlterTable $at */
        $at = $this->getMockBuilder(AlterTable::class)
                    ->onlyMethods($atMockMethods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $name = 'foo';
        $type = null;
        $e = new AlterColumn($at, $name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertNull($e->getType());
        $this->assertInstanceOf(AlterTable::class, $e->getTable());
        $this->assertEquals($at, $e->getTable());

        $name = 'foo';
        $type = 'int';
        $e = new AlterColumn($at, $name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertEquals($type, $e->getType());
        $this->assertEquals($at, $e->getTable());
        $this->assertInstanceOf(AlterTable::class, $e->getTable());
    }

    public function testDefaultValue(): void
    {
         $atMockMethods = $this->getClassMethodsToMock(
             AlterTable::class,
             []
         );

        /** @var AlterTable $at */
        $at = $this->getMockBuilder(AlterTable::class)
                    ->onlyMethods($atMockMethods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $column = 'foo';
        $name = 'default';
        $value = 1;

        $e = new AlterColumn($at, $column);

        $bc = $e->defaultValue($value);

        $this->assertInstanceOf(BaseColumn::class, $bc);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($value, $e->get($name));
        $this->assertArrayHasKey($name, $props);

        $e = new AlterColumn($at, $column);

        $e->set('handleDefault', false);

        $bc = $e->defaultValue($value);

        $this->assertInstanceOf(BaseColumn::class, $bc);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertFalse($e->has($name));
    }

    public function testAutoincrement(): void
    {
        $atMockMethods = $this->getClassMethodsToMock(
            AlterTable::class,
            []
        );

        /** @var AlterTable $at */
        $at = $this->getMockBuilder(AlterTable::class)
                    ->onlyMethods($atMockMethods)
                    ->disableOriginalConstructor()
                    ->getMock();

        $column = 'foo';
        $name = 'autoincrement';

        $e = new AlterColumn($at, $column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->autoincrement();

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertTrue($e->get($name));
        $this->assertArrayHasKey($name, $props);
    }
}
