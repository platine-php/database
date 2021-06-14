<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\BaseColumn;
use Platine\Dev\PlatineTestCase;

/**
 * BaseColumn class tests
 *
 * @group core
 * @group database
 */
class BaseColumnTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $name = 'foo';
        $type = null;
        $e = new BaseColumn($name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertNull($e->getType());

        $name = 'foo';
        $type = 'int';
        $e = new BaseColumn($name, $type);

        $this->assertEquals($name, $e->getName());
        $this->assertEquals($type, $e->getType());
    }

    public function testSetType(): void
    {
        $name = 'foo';
        $e = new BaseColumn($name, null);

        $this->assertNull($e->getType());

        $type = 'int';
        $e->setType($type);
        $this->assertEquals($type, $e->getType());
    }

    public function testProperties(): void
    {
        $column = 'foo';
        $name = 'size';
        $value = 'small';

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));
        $this->assertEquals(100, $e->get($name, 100));

        $e->set($name, $value);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($value, $e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testSize(): void
    {
        $column = 'foo';
        $value = 'foo_small';
        $name = 'size';

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->size($value);

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e = new BaseColumn($column);
        $value = 'small';
        $e->size($value);
        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($value, $e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testNotNull(): void
    {
        $column = 'foo';
        $name = 'nullable';

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->notNull();

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertFalse($e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testDescription(): void
    {
        $column = 'foo';
        $name = 'description';
        $comment = 'my description';

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->description($comment);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($comment, $e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testDefault(): void
    {
        $column = 'foo';
        $name = 'default';
        $value = 1;

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->defaultValue($value);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($value, $e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testUnsigned(): void
    {
        $column = 'foo';
        $name = 'unsigned';

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->unsigned(false);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertFalse($e->get($name));
        $this->assertArrayHasKey($name, $props);

        $e->unsigned(true);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertTrue($e->get($name));
        $this->assertArrayHasKey($name, $props);
    }

    public function testLength(): void
    {
        $column = 'foo';
        $name = 'length';
        $value = 255;

        $e = new BaseColumn($column);

        $props = $e->getProperties();

        $this->assertEmpty($props);
        $this->assertFalse($e->has($name));
        $this->assertNull($e->get($name));

        $e->length($value);

        $props = $e->getProperties();

        $this->assertCount(1, $props);
        $this->assertTrue($e->has($name));
        $this->assertEquals($value, $e->get($name));
        $this->assertArrayHasKey($name, $props);
    }
}
