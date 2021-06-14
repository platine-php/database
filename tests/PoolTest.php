<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Connection;
use Platine\Database\Exception\ConnectionAlreadyExistsException;
use Platine\Database\Exception\ConnectionNotFoundException;
use Platine\Database\Pool;
use Platine\Dev\PlatineTestCase;

/**
 * Pool class tests
 *
 * @group core
 * @group database
 */
class PoolTest extends PlatineTestCase
{
    public function testConstructorEmptyConfig(): void
    {
        $e = new Pool([]);

        $rd = $this->getPrivateProtectedAttribute(Pool::class, 'default');
        $rc = $this->getPrivateProtectedAttribute(Pool::class, 'connections');

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('default', $default);
        $this->assertCount(0, $connections);
        $this->assertFalse($e->has('foo'));
    }

    public function testConstructorNotEmptyConfig(): void
    {
        $cfg = $this->getDefaultDbConnectionConfig();
        $e = new Pool($cfg);

        $rd = $this->getPrivateProtectedAttribute(Pool::class, 'default');
        $rc = $this->getPrivateProtectedAttribute(Pool::class, 'connections');

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('master', $default);
        $this->assertCount(2, $connections);
        $this->assertFalse($e->has('foo'));
        $this->assertTrue($e->has('master'));
        $this->assertTrue($e->has('slave'));
    }

    public function testAddConnectionSuccess(): void
    {
        $cfg = $this->getAddDbConnectionConfigs();
        $this->assertCount(2, $cfg);

        $e = new Pool();

        $rd = $this->getPrivateProtectedAttribute(Pool::class, 'default');
        $rc = $this->getPrivateProtectedAttribute(Pool::class, 'connections');

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('default', $default);
        $this->assertCount(0, $connections);

        $e->addConnection($cfg[0]);
        $e->addConnection($cfg[1]);

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('master', $default);
        $this->assertCount(2, $connections);
        $this->assertFalse($e->has('foo'));
        $this->assertTrue($e->has('master'));
        $this->assertTrue($e->has('slave'));

        $this->assertEquals($e->getConnection(), $e->getConnection('master'));
        $this->assertInstanceOf(Connection::class, $e->getConnection('master'));
        $this->assertInstanceOf(Connection::class, $e->getConnection('slave'));
    }

    public function testAddConnectionAlreadyExist(): void
    {
        $cfg = $this->getAddDbConnectionConfigs();
        $this->assertCount(2, $cfg);

        $e = new Pool();

        $rd = $this->getPrivateProtectedAttribute(Pool::class, 'default');
        $rc = $this->getPrivateProtectedAttribute(Pool::class, 'connections');

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('default', $default);
        $this->assertCount(0, $connections);

        $e->addConnection($cfg[0]);

        $this->expectException(ConnectionAlreadyExistsException::class);
        $e->addConnection($cfg[0]);
    }

    public function testGetConnectionNotExist(): void
    {
        $e = new Pool();

        $this->expectException(ConnectionNotFoundException::class);
        $e->getConnection('name_not_exists');
    }

    public function testRemoveConnection(): void
    {
        $cfg = $this->getAddDbConnectionConfigs();
        $this->assertCount(2, $cfg);

        $e = new Pool();

        $rd = $this->getPrivateProtectedAttribute(Pool::class, 'default');
        $rc = $this->getPrivateProtectedAttribute(Pool::class, 'connections');

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('default', $default);
        $this->assertCount(0, $connections);

        $e->addConnection($cfg[0]);
        $e->addConnection($cfg[1]);

        $default = $rd->getValue($e);
        $connections = $rc->getValue($e);

        $this->assertEquals('master', $default);
        $this->assertCount(2, $connections);
        $this->assertFalse($e->has('foo'));
        $this->assertTrue($e->has('master'));
        $this->assertTrue($e->has('slave'));

        $e->remove('master');
        $this->assertFalse($e->has('master'));

        $this->expectException(ConnectionNotFoundException::class);
        $e->setDefault('master');
    }

    private function getDefaultDbConnectionConfig(): array
    {
        return [
                'default' => 'master',
                'connections' => [
                    'master' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:'
                    ],
                    'slave' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:'
                    ]
                ]
        ];
    }

    private function getAddDbConnectionConfigs(): array
    {
        return [
                    [
                        'name' => 'master',
                        'driver' => 'sqlite',
                        'database' => ':memory:'
                    ],
                    [
                        'name' => 'slave',
                        'driver' => 'sqlite',
                        'database' => ':memory:'
                    ]
                ];
    }
}
