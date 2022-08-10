<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Schema\ForeignKey;
use Platine\Dev\PlatineTestCase;

/**
 * ForeignKey class tests
 *
 * @group core
 * @group database
 */
class ForeignKeyTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $columns = ['foo'];
        $e = new ForeignKey($columns);

        $this->assertCount(1, $e->getColumns());
        $this->assertContains('foo', $e->getColumns());
    }

    public function testReferences(): void
    {
        $columns = ['foo'];
        $e = new ForeignKey($columns);

        $table = 'bar';

        $e->references($table, 'baz');
        $this->assertCount(1, $e->getReferenceColumns());
        $this->assertContains('baz', $e->getReferenceColumns());
        $this->assertEquals($table, $e->getReferenceTable());

        $e = new ForeignKey($columns);

        $table = 'bar';

        $e->references($table, 'baz', 'bazz');
        $this->assertCount(2, $e->getReferenceColumns());
        $this->assertContains('baz', $e->getReferenceColumns());
        $this->assertContains('bazz', $e->getReferenceColumns());
        $this->assertEquals($table, $e->getReferenceTable());
    }

    public function testOnDeleteAndOnUpdate(): void
    {
        $this->onDeleteUpdateTests('onDelete', 'ON DELETE');
        $this->onDeleteUpdateTests('onUpdate', 'ON UPDATE');
    }

    private function onDeleteUpdateTests(string $method, string $key): void
    {
        $columns = ['foo'];
        $e = new ForeignKey($columns);

        $actions = $e->getActions();
        $this->assertEmpty($actions);

        $e->{$method}('ERROR');

        $actions = $e->getActions();
        $this->assertEmpty($actions);

        $action = 'NO ACTION';
        $e->{$method}($action);

        $actions = $e->getActions();

        $this->assertCount(1, $actions);
        $this->assertArrayHasKey($key, $actions);
        $this->assertEquals($action, $actions[$key]);
    }
}
