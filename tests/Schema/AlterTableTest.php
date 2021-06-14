<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\ForeignKey;
use Platine\Dev\PlatineTestCase;

/**
 * AlterTable class tests
 *
 * @group core
 * @group database
 */
class AlterTableTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $table = 'foo';
        $e = new AlterTable($table);

        $this->assertEquals($table, $e->getTableName());
    }

    public function testForeign(): void
    {
        $table = 'foo';
        $column = 'foo_column';
        $name = 'fk_name';
        $expectedName = 'fk_name';

        $e = new AlterTable($table);

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        $e->foreign($column, $name);

        $commands = $e->getCommands();

        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], 'addForeign');

        $data = $commands[0]['data'];

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('foreign', $data);

        $this->assertEquals($data['name'], $expectedName);
        $this->assertInstanceOf(ForeignKey::class, $data['foreign']);


        //name is null
        $e = new AlterTable($table);
        $e->foreign($column, null);

        $commands = $e->getCommands();

        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], 'addForeign');

        $data = $commands[0]['data'];

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('foreign', $data);

        $this->assertEquals($data['name'], 'foo_fk_foo_column');
        $this->assertInstanceOf(ForeignKey::class, $data['foreign']);
    }

    public function testSetDefaultValue(): void
    {
        $table = 'foo';
        $e = new AlterTable($table);

        $column = 'foo_column';
        $value = 1;

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        $e->setDefaultValue($column, $value);

        $commands = $e->getCommands();
        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], 'setDefaultValue');

        $data = $commands[0]['data'];

        $this->assertArrayHasKey('column', $data);
        $this->assertArrayHasKey('value', $data);

        $this->assertEquals($data['column'], $column);
        $this->assertEquals($data['value'], $value);
    }

    public function testRenameColumn(): void
    {
        $table = 'foo';
        $e = new AlterTable($table);

        $from = 'foo_column';
        $to = 'bar_column';

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        $e->renameColumn($from, $to);

        $commands = $e->getCommands();
        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], 'renameColumn');

        $data = $commands[0]['data'];

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('column', $data);

        $this->assertEquals($data['from'], $from);
        $this->assertInstanceOf(AlterColumn::class, $data['column']);
    }


    /**
     * @dataProvider addCommandsSimpleDataProvider
     * @return void
     */
    public function testAddCommandsSimple(
        string $method,
        string $key,
        string $name
    ): void {
        $table = 'foo';
        $e = new AlterTable($table);

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        $e->{$method}($name);

        $commands = $e->getCommands();
        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], $key);
        $this->assertEquals($commands[0]['data'], $name);
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

        $e = new AlterTable($table);

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        /** @var AlterColumn $col */
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

        $this->assertInstanceOf(AlterColumn::class, $col);
        $this->assertEquals($col->getName(), $name);
        $this->assertEquals($col->getType(), $type);

        $commands = $e->getCommands();
        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);

        $this->assertEquals($commands[0]['type'], 'addColumn');
        $this->assertInstanceOf(AlterColumn::class, $commands[0]['data']);
    }

    /**
     * @dataProvider modifyColumnSimpleDataProvider
     * @return void
     */
    public function testModifyColumnsSimple(
        string $method,
        string $type,
        $useLength = false,
        $usePrecision = false,
        $precision = 1,
        $length = 100
    ): void {
        $table = 'foo';
        $name = 'my_column';

        $e = new AlterTable($table);

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        /** @var AlterColumn $col */
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

        $this->assertInstanceOf(AlterColumn::class, $col);
        $this->assertEquals($col->getName(), $name);
        $this->assertEquals($col->getType(), $type);

        $commands = $e->getCommands();
        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);

        $this->assertEquals($commands[0]['type'], 'modifyColumn');
        $this->assertInstanceOf(AlterColumn::class, $commands[0]['data']);
    }

    /**
     * @dataProvider addKeySimpleDataProvider
     * @return void
     */
    public function testAddKeysSimple(
        string $method,
        string $key,
        $column,
        ?string $name,
        $expectedColumns,
        string $expectedName,
        string $table
    ): void {
        $e = new AlterTable($table);

        $commands = $e->getCommands();
        $this->assertEmpty($commands);

        $e->{$method}($column, $name);

        $commands = $e->getCommands();

        $this->assertCount(1, $commands);
        $this->assertArrayHasKey('type', $commands[0]);
        $this->assertArrayHasKey('data', $commands[0]);
        $this->assertEquals($commands[0]['type'], $key);

        $data = $commands[0]['data'];

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('columns', $data);

        $this->assertEquals($data['name'], $expectedName);
        $this->assertEquals($data['columns'], $expectedColumns);
    }

    /**
     * Data provider for "testModifyColumnsSimple"
     * @return array
     */
    public function modifyColumnSimpleDataProvider(): array
    {
        return array(
            array('toInteger', 'integer', false, false, 1, 200),
            array('toFloat', 'float', false, false, 1, 200),
            array('toDouble', 'double', false, false, 1, 200),
            array('toDecimal', 'decimal', true, false, 1, 200),
            array('toDecimal', 'decimal', true, true, 1, 200),
            array('toBoolean', 'boolean', false, false, 1, 200),
            array('toBinary', 'binary', false, false, 1, 200),
            array('toString', 'string', true, false, 1, 200),
            array('toFixed', 'fixed', true, false, 1, 200),
            array('toText', 'text', false, false, 1, 200),
            array('toTime', 'time', false, false, 1, 200),
            array('toTimestamp', 'timestamp', false, false, 1, 200),
            array('toDate', 'date', false, false, 1, 200),
            array('toDatetime', 'datetime', false, false, 1, 200),
        );
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

    /**
     * Data provider for "testAddCommandsSimple"
     * @return array
     */
    public function addCommandsSimpleDataProvider(): array
    {
        return array(
            array('dropIndex', 'dropIndex', 'foo_name'),
            array('dropUnique', 'dropUniqueKey', 'foo_name'),
            array('dropPrimary', 'dropPrimaryKey', 'foo_name'),
            array('dropForeign', 'dropForeignKey', 'foo_name'),
            array('dropColumn', 'dropColumn', 'foo_name'),
            array('dropDefaultValue', 'dropDefaultValue', 'foo_name'),
        );
    }

    /**
     * Data provider for "testAddKeysSimple"
     * @return array
     */
    public function addKeySimpleDataProvider(): array
    {
        return array(
            array('primary', 'addPrimary', 'foo_column', 'pk_name', ['foo_column'], 'pk_name', 'foo'),
            array('primary', 'addPrimary', 'foo_column', null, ['foo_column'], 'foo_pk_foo_column', 'foo'),
            array('unique', 'addUnique', 'foo_column', null, ['foo_column'], 'foo_uk_foo_column', 'foo'),
            array('unique', 'addUnique', 'foo_column', 'uk_name', ['foo_column'], 'uk_name', 'foo'),
            array('index', 'addIndex', 'foo_column', 'ik_name', ['foo_column'], 'ik_name', 'foo'),
            array('index', 'addIndex', 'foo_column', null, ['foo_column'], 'foo_ik_foo_column', 'foo'),

        );
    }
}
