<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Database\Schema\ForeignKey;
use Platine\PlatineTestCase;

/**
 * CreateTable class tests
 *
 * @group core
 * @group database
 */
class CreateTableTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $this->assertEquals($table, $e->getTableName());
    }

    public function testEngine(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $o = $e->getEngine();
        $this->assertNull($o);

        $e->engine('foo');

        $this->assertEquals('foo', $e->getEngine());
    }

    public function testPrimary(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $o = $e->getPrimaryKey();
        $this->assertEmpty($o);

        //Column is string and name is null
        $columns = 'foo';
        $name = null;
        $expectedName = $table . '_pk_' . $columns;
        $e->primary($columns, $name);

        $o = $e->getPrimaryKey();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey('name', $o);
        $this->assertArrayHasKey('columns', $o);

        $this->assertEquals([$columns], $o['columns']);
        $this->assertEquals($expectedName, $o['name']);

        //Column is string and name is set
        $columns = 'foo';
        $name = 'foo_pk_name';
        $expectedName = 'foo_pk_name';
        $e->primary($columns, $name);

        $o = $e->getPrimaryKey();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey('name', $o);
        $this->assertArrayHasKey('columns', $o);

        $this->assertEquals([$columns], $o['columns']);
        $this->assertEquals($expectedName, $o['name']);

        //Column is array and name is null
        $columns = ['foo', 'bar'];
        $name = null;
        $expectedName = $table . '_pk_foo_bar';
        $e->primary($columns, $name);

        $o = $e->getPrimaryKey();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey('name', $o);
        $this->assertArrayHasKey('columns', $o);

        $this->assertEquals($columns, $o['columns']);
        $this->assertEquals($expectedName, $o['name']);
    }


    public function testUnique(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $o = $e->getUniqueKeys();
        $this->assertEmpty($o);

        //Column is string and name is null
        $columns = 'foo';
        $name = null;
        $expectedName = $table . '_uk_' . $columns;
        $e->unique($columns, $name);

        $o = $e->getUniqueKeys();

        $this->assertCount(1, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals([$columns], $o[$expectedName]);

        //Column is string and name is set
        $columns = 'foo';
        $name = 'foo_uk_name';
        $expectedName = 'foo_uk_name';
        $e->unique($columns, $name);

        $o = $e->getUniqueKeys();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals([$columns], $o[$expectedName]);

        //Column is array and name is null
        $columns = ['foo', 'bar'];
        $name = null;
        $expectedName = $table . '_uk_foo_bar';
        $e->unique($columns, $name);

        $o = $e->getUniqueKeys();

        $this->assertCount(3, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals($columns, $o[$expectedName]);
    }

    public function testIndex(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $o = $e->getIndexes();
        $this->assertEmpty($o);

        //Column is string and name is null
        $columns = 'foo';
        $name = null;
        $expectedName = $table . '_ik_' . $columns;
        $e->index($columns, $name);

        $o = $e->getIndexes();

        $this->assertCount(1, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals([$columns], $o[$expectedName]);

        //Column is string and name is set
        $columns = 'foo';
        $name = 'foo_ik_name';
        $expectedName = 'foo_ik_name';
        $e->index($columns, $name);

        $o = $e->getIndexes();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals([$columns], $o[$expectedName]);

        //Column is array and name is null
        $columns = ['foo', 'bar'];
        $name = null;
        $expectedName = $table . '_ik_foo_bar';
        $e->index($columns, $name);

        $o = $e->getIndexes();

        $this->assertCount(3, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertEquals($columns, $o[$expectedName]);
    }

    public function testForeignKeys(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $o = $e->getForeignKeys();
        $this->assertEmpty($o);

        //Column is string and name is null
        $columns = 'foo';
        $name = null;
        $expectedName = $table . '_fk_' . $columns;
        $e->foreign($columns, $name);

        $o = $e->getForeignKeys();

        $this->assertCount(1, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertInstanceOf(ForeignKey::class, $o[$expectedName]);

        //Column is string and name is set
        $columns = 'foo';
        $name = 'foo_fk_name';
        $expectedName = 'foo_fk_name';
        $e->foreign($columns, $name);

        $o = $e->getForeignKeys();

        $this->assertCount(2, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertInstanceOf(ForeignKey::class, $o[$expectedName]);

        //Column is array and name is null
        $columns = ['foo', 'bar'];
        $name = null;
        $expectedName = $table . '_fk_foo_bar';
        $e->foreign($columns, $name);

        $o = $e->getForeignKeys();

        $this->assertCount(3, $o);
        $this->assertArrayHasKey($expectedName, $o);

        $this->assertInstanceOf(ForeignKey::class, $o[$expectedName]);
    }

    public function testAutoincrement(): void
    {
        $table = 'foo';
        $e = new CreateTable($table);

        $column = 'baz';
        $type = 'text';
        $cc = new CreateColumn($e, $column, $type);

        $e->autoincrement($cc);

        $props = $cc->getProperties();

        $this->assertNull($e->getAutoincrement());
        $this->assertEmpty($props);

        $e = new CreateTable($table);

        $column = 'baz';
        $type = 'integer';
        $cc = new CreateColumn($e, $column, $type);

        $e->autoincrement($cc);

        $props = $cc->getProperties();
        $this->assertTrue($e->getAutoincrement());
        $this->assertCount(1, $props);

        $this->assertArrayHasKey('autoincrement', $props);
        $this->assertTrue($props['autoincrement']);

        $prim = $e->getPrimaryKey();

        $this->assertCount(2, $prim);
        $this->assertArrayHasKey('name', $prim);
        $this->assertArrayHasKey('columns', $prim);

        $this->assertEquals([$column], $prim['columns']);
        $this->assertEquals($table . '_pk_' . $column, $prim['name']);
    }

    public function testSoftDelete(): void
    {
        $table = 'foo';
        $name = 'my_column';

        $e = new CreateTable($table);

        $columns = $e->getColumns();
        $this->assertEmpty($columns);


        $e->softDelete($name);

        $columns = $e->getColumns();
        $this->assertCount(1, $columns);
        $this->assertArrayHasKey($name, $columns);

        /** @var CreateColumn $col */
        $col = $columns[$name];
        $this->assertInstanceOf(CreateColumn::class, $col);
        $this->assertEquals($name, $col->getName());
        $this->assertEquals('datetime', $col->getType());
        $this->assertNull($col->get('nullable'));
    }

    public function testTimestamps(): void
    {
        $table = 'foo';
        $cColumn = 'my_cre_column';
        $uColumn = 'my_up_column';

        $e = new CreateTable($table);

        $columns = $e->getColumns();
        $this->assertEmpty($columns);


        $e->timestamps($cColumn, $uColumn);

        $columns = $e->getColumns();
        $this->assertCount(2, $columns);
        $this->assertArrayHasKey($cColumn, $columns);
        $this->assertArrayHasKey($uColumn, $columns);

        /** @var CreateColumn $cCol */
        $cCol = $columns[$cColumn];

        /** @var CreateColumn $uCol */
        $uCol = $columns[$uColumn];

        $this->assertInstanceOf(CreateColumn::class, $cCol);
        $this->assertInstanceOf(CreateColumn::class, $uCol);

        $this->assertEquals($cColumn, $cCol->getName());
        $this->assertEquals('datetime', $cCol->getType());
        $this->assertFalse($cCol->get('nullable'));

        $this->assertEquals($uColumn, $uCol->getName());
        $this->assertEquals('datetime', $uCol->getType());
        $this->assertNull($uCol->get('nullable'));
    }

    /**
     * @dataProvider addColumnSimpleDataProvider
     * @return void
     */
    public function testAddColumnsSimple(
        string $method,
        string $type,
        $useLength = false,
        $usePrecision = false,
        $precision = 1,
        $length = 100
    ): void {
        $table = 'foo';
        $name = 'my_column';

        $e = new CreateTable($table);

        $columns = $e->getColumns();
        $this->assertEmpty($columns);

        /** @var CreateColumn $col */
        if ($usePrecision) {
            $col = $e->{$method}($name, $length, $precision);
            $this->assertEquals($col->get('precision'), $precision);
            $this->assertEquals($col->get('length'), $length);
        } elseif ($useLength) {
            $col = $e->{$method}($name, $length);
            $this->assertEquals($col->get('length'), $length);
        } else {
            $col = $e->{$method}($name);
        }

        $this->assertInstanceOf(CreateColumn::class, $col);
        $this->assertEquals($col->getName(), $name);
        $this->assertEquals($col->getType(), $type);

        $columns = $e->getColumns();
        $this->assertCount(1, $columns);
    }

    /**
     * Data provider for "testAddColumnsSimple"
     * @return array
     */
    public function addColumnSimpleDataProvider(): array
    {
        return array(
            array('integer', 'integer', false, false, 1, 200),
            array('float', 'float', false, false, 1, 200),
            array('double', 'double', false, false, 1, 200),
            array('decimal', 'decimal', true, false, 1, 200),
            array('decimal', 'decimal', true, true, 1, 200),
            array('boolean', 'boolean', false, false, 1, 200),
            array('binary', 'binary', false, false, 1, 200),
            array('string', 'string', true, false, 1, 200),
            array('fixed', 'fixed', true, false, 1, 200),
            array('text', 'text', false, false, 1, 200),
            array('time', 'time', false, false, 1, 200),
            array('timestamp', 'timestamp', false, false, 1, 200),
            array('date', 'date', false, false, 1, 200),
            array('datetime', 'datetime', false, false, 1, 200),
        );
    }
}
