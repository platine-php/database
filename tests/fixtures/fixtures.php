<?php

declare(strict_types=1);

namespace Platine\Test\Fixture;

use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\Generator;
use Platine\Database\Configuration;
use Platine\Database\ConfigurationInterface;
use Platine\Database\Connection as RealConnection;
use Platine\Database\ResultSet;
use Platine\Database\Schema as RealSchema;
use Platine\PlatineTestCase;

class Schema extends RealSchema
{
    public function getDatabaseName(): string
    {
        return 'db';
    }
}


class Connection extends RealConnection
{

    private string $rawSql = '';

    public function __construct($driver)
    {
        $driverClass = '\\Platine\\Database\\Driver\\' . $driver;
        $this->driver = new $driverClass($this);
    }

    public function getRawSql(): string
    {
        return $this->rawSql;
    }

    public function query(string $sql, array $params = []): ResultSet
    {
        $this->rawSql = $this->replaceParameters($sql, $params);

        $stmt = (new Generator())->getMock(PDOStatement::class);

        return new ResultSet($stmt);
    }

    public function column(string $sql, array $params = [])
    {
        $this->rawSql =  $this->replaceParameters($sql, $params);

        return 1;
    }

    public function count(string $sql, array $params = []): int
    {
        $this->rawSql =  $this->replaceParameters($sql, $params);

        return 0;
    }

    public function exec(string $sql, array $params = []): bool
    {
        $this->rawSql = $this->replaceParameters($sql, $params);

        return true;
    }

    public function getSchema(): Schema
    {
        return new Schema($this);
    }

    public function transaction(
        callable $callback,
        $that = null
    ) {
        return $callback($that);
    }
}

class PlatineTestCaseDb extends PlatineTestCase
{

    protected function getDbConnectionConfig(): ConfigurationInterface
    {
        return new Configuration([
                    'name' => 'master',
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'persistent' => true
                ]);
    }

    protected function getDbConnectionConfigOK(): ConfigurationInterface
    {
        return new Configuration([
                    'name' => 'master',
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'persistent' => true,
                    'commands' => [
                        'PRAGMA sqlite_master'
                    ]
                ]);
    }

    protected function loadTestsData(PDO $pdo): void
    {
        $this->assertTrue($pdo->exec('drop table if exists tests') >= 0);

        $this->assertTrue($pdo->exec('create table tests('
                . 'id integer primary key autoincrement, name text)') >= 0);


        $data = [
            'foo',
            'bar',
            'baz',
            'baar',
        ];

        $i = 0;
        foreach ($data as $d) {
            $r = $pdo->exec(sprintf('insert into tests(name) values("%s")', $d));

            if ($r) {
                $i++;
            }
        }

        $this->assertEquals(4, $i);
    }
}
