<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\ConnectionStatement;
use Platine\Dev\PlatineTestCase;

/**
 * ConnectionStatement class tests
 *
 * @group core
 * @group database
 */
class ConnectionStatementTest extends PlatineTestCase
{
    public function testCloseCursor(): void
    {
        $o = new ConnectionStatement();

        $this->assertTrue($o->closeCursor());
    }

    public function testExecute(): void
    {
        $o = new ConnectionStatement();

        $this->assertFalse($o->execute());
    }

    public function testRowCount(): void
    {
        $o = new ConnectionStatement();

        $this->assertEquals(0, $o->rowCount());
    }
}
