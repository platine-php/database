<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Configuration;
use Platine\Database\Driver\MySQL;
use Platine\Database\Driver\Oracle;
use Platine\Database\Driver\PostgreSQL;
use Platine\Database\Driver\SQLite;
use Platine\Database\Driver\SQLServer;
use Platine\Dev\PlatineTestCase;

/**
 * Configuration class tests
 *
 * @group core
 * @group database
 */
class ConfigurationTest extends PlatineTestCase
{
    public function testDefaultValues(): void
    {
        $e = new Configuration([]);

        $this->assertEmpty($e->getAttributes());
        $this->assertEmpty($e->getCommands());
        $this->assertEmpty($e->getAppname());
        $this->assertEmpty($e->getUsername());
        $this->assertEmpty($e->getPassword());
        $this->assertEmpty($e->getDatabase());
        $this->assertEmpty($e->getSocket());
        $this->assertCount(4, $e->getOptions());

        $this->assertNull($e->getPort());

        $this->assertFalse($e->isPersistent());
        $this->assertFalse($e->hasAttribute('foo'));

        $this->assertEquals('mysql', $e->getDriverName());
        $this->assertEquals('default', $e->getName());
        $this->assertEquals('UTF8', $e->getCharset());
        $this->assertEquals('utf8_general_ci', $e->getCollation());
        $this->assertEquals('localhost', $e->getHostname());
        $this->assertEquals(MySQL::class, $e->getDriverClassName());
    }

    public function testLoad(): void
    {
        $cfg = [
            'name' => 'foo_name',
            'driver' => 'mysql',
            'database' => 'db_test',
            'hostname' => '127.0.0.1',
            'port' => 3307,
            'username' => 'root',
            'password' => '',
            'persistent' => true,
            'options' => [
                'foo' => 'bar'
            ],
            'attributes' => [
                'foo' => 'bar'
            ],
            'commands' => [
                'foo'
            ]
        ];

        $e = new Configuration($cfg);

        $this->assertEmpty($e->getAppname());
        $this->assertEmpty($e->getPassword());
        $this->assertEmpty($e->getSocket());
        $this->assertCount(5, $e->getOptions());
        $this->assertCount(1, $e->getCommands());
        $this->assertCount(1, $e->getAttributes());


        $this->assertTrue($e->isPersistent());
        $this->assertTrue($e->hasAttribute('foo'));

        $this->assertContains('foo', $e->getCommands());

        $this->assertArrayHasKey('foo', $e->getOptions());
        $this->assertArrayHasKey('foo', $e->getAttributes());

        $this->assertEquals('bar', $e->getAttribute('foo'));
        $this->assertEquals(123, $e->getAttribute('fooz', 123));

        $this->assertEquals('mysql', $e->getDriverName());
        $this->assertEquals('foo_name', $e->getName());
        $this->assertEquals('db_test', $e->getDatabase());
        $this->assertEquals('UTF8', $e->getCharset());
        $this->assertEquals('utf8_general_ci', $e->getCollation());
        $this->assertEquals('root', $e->getUsername());
        $this->assertEquals('127.0.0.1', $e->getHostname());
        $this->assertEquals(3307, $e->getPort());
        $this->assertEquals(MySQL::class, $e->getDriverClassName());
    }

    /**
     * @dataProvider getDriverClassNameDataProvider
     *
     * @param string $driverName
     * @param string $class
     * @return void
     */
    public function testGetDriverClassName(string $driverName, string $class): void
    {
        $cfg = [
            'driver' => $driverName
        ];

        $e = new Configuration($cfg);

        $this->assertEquals($class, $e->getDriverClassName());
    }

    /**
     * Data provider for "testGetDriverClassName"
     * @return array
     */
    public function getDriverClassNameDataProvider(): array
    {
        return array(
            array('mysql', MySQL::class),
            array('sqlite', SQLite::class),
            array('sqlsrv', SQLServer::class),
            array('oci', Oracle::class),
            array('oracle', Oracle::class),
            array('pgsql', PostgreSQL::class),
        );
    }
}
