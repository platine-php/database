<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use InvalidArgumentException;
use PDO;
use Platine\Database\Configuration;
use Platine\Database\Connection;
use Platine\Database\Driver\SQLite;
use Platine\Database\Exception\ConnectionException;
use Platine\Database\Exception\QueryException;
use Platine\Database\Exception\QueryPrepareException;
use Platine\Database\Exception\TransactionException;
use Platine\Database\ResultSet;
use Platine\Database\Schema;
use Platine\Logger\Logger;
use Platine\Test\Fixture\PlatineTestCaseDb;

/**
 * Connection class tests
 *
 * @group core
 * @group database
 */
class ConnectionTest extends PlatineTestCaseDb
{
    public function testConstructor(): void
    {
        $cfg = $this->getDbConnectionConfig();

        $e = new Connection($cfg);

        $this->assertInstanceOf(Connection::class, $e);
        $this->assertInstanceOf(Schema::class, $e->getSchema());
        $this->assertInstanceOf(PDO::class, $e->getPDO());
        $this->assertInstanceOf(SQLite::class, $e->getDriver());
        $this->assertInstanceOf(Configuration::class, $e->getConfig());

        $this->assertEquals('sqlite::memory:', $e->getDsn());

        $this->assertEmpty($e->getLogs());

        $this->assertCount(2, $e->getParams());
        $this->assertArrayHasKey('driver', $e->getParams());
        $this->assertContains(':memory:', $e->getParams());

        $l = $this->getMockInstance(Logger::class);

        $e->setLogger($l);

        $rl = $this->getPrivateProtectedAttribute(Connection::class, 'logger');

        $logger = $rl->getValue($e);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals($l, $logger);
    }



    public function testConnectionAttributesNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Connection(new Configuration(['driver' => 'foo_driver'])));
    }

    public function testCannotConnectMySQL(): void
    {
        $this->expectException(ConnectionException::class);
        (new Connection(new Configuration([
            'driver' => 'mysql',
            'hostname' => 'foo_hostname',
            'username' => 'foo_username',
            'port' => 45656,
            'database' => 'foo_db_does_not_exists',
            'socket' => 'foo_db_does_not_exists',
        ])));
    }

    public function testCannotConnectPostgreSQL(): void
    {
        $this->expectException(ConnectionException::class);
        (new Connection(new Configuration([
            'driver' => 'pgsql',
            'hostname' => 'foo_hostname',
            'username' => 'foo_username',
            'port' => 45656,
            'database' => 'foo_db_does_not_exists',
        ])));
    }

    public function testCannotConnectOracle(): void
    {
        $this->expectException(ConnectionException::class);
        (new Connection(new Configuration([
            'driver' => 'oracle',
            'hostname' => 'foo_hostname',
            'username' => 'foo_username',
            'port' => 45656,
            'database' => 'foo_db_does_not_exists',
        ])));
    }

    public function testCannotConnectSQLServer(): void
    {
        $this->expectException(ConnectionException::class);
        (new Connection(new Configuration([
            'driver' => 'sqlsrv',
            'hostname' => 'foo_hostname',
            'username' => 'foo_username',
            'port' => 45656,
            'database' => 'foo_db_does_not_exists',
            'appname' => 'my_foo_apps',
            'attributes' => [
                'login_timeout' => 12
            ]
        ])));
    }

    public function testEmulation(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);
        $this->assertFalse($e->getEmulate());

        $e->setEmulate(true);
        $this->assertTrue($e->getEmulate());
    }

    public function testQuery(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $rs = $e->query('select * from tests');



        $this->assertInstanceOf(ResultSet::class, $rs);
        $this->assertEquals(1, $rs->column(0));
        $this->assertEquals('bar', $rs->column(1));
        $this->assertEquals('select * from tests', $e->getSql()[0]);
        $this->assertEmpty($e->getValues()[0]);

        $rsp = $e->query('select * from tests where id = ?', [3]);
        $this->assertInstanceOf(ResultSet::class, $rsp);
        $this->assertEquals(3, $rsp->column(0));

        $this->assertTrue($e->exec('select * from tests'));

        //TODO SQLite Driver always return 0 for PDOStatement::rowCount()
        //$this->assertEquals(4, $e->count('select * from tests'));
        $this->assertEquals(0, $e->count('select * from tests'));

        $this->assertEquals(1, $e->column('select id from tests where id = ?', [1]));
        $this->assertFalse($e->column('select id from tests where id in (?,?)', [null, false]));
    }

    public function testQueryUsingEmulation(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $e->setEmulate(true);

        $rs = $e->query('select * from tests');



        $this->assertInstanceOf(ResultSet::class, $rs);
        $this->assertFalse($rs->column(0));
        $this->assertFalse($rs->column(1));
        $this->assertEquals('select * from tests', $e->getSql()[0]);
        $this->assertEmpty($e->getValues()[0]);

        $e->resetSqlValues();
        $this->assertEmpty($e->getSql());
        $this->assertEmpty($e->getValues());

        $e->setEmulate(false);
        $rsp = $e->query('select * from tests where id = ?', [3]);
        $this->assertInstanceOf(ResultSet::class, $rsp);
        $this->assertEquals(3, $rsp->column(0));

        $this->assertTrue($e->exec('select * from tests'));

        //TODO SQLite Driver always return 0 for PDOStatement::rowCount()
        //$this->assertEquals(4, $e->count('select * from tests'));
        $this->assertEquals(0, $e->count('select * from tests'));

        $this->assertEquals(1, $e->column('select id from tests where id = ?', [1]));
        $this->assertFalse($e->column('select id from tests where id in (?,?)', [null, false]));
    }

    public function testSerialization(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e1 = new Connection($cfg);

        $serialize = serialize($e1);

        $this->assertNotEmpty($serialize);

        $cnx = unserialize($serialize);

        $this->loadTestsData($cnx->getPDO());

        $rs = $cnx->query('select * from tests');

        $this->assertInstanceOf(ResultSet::class, $rs);
        $this->assertEquals(1, $rs->column(0));
        $this->assertEquals('bar', $rs->column(1));
    }

    public function testQueryPrepareError(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $this->expectException(QueryPrepareException::class);
        $e->query('select * from tests where name_foo = ?', [1]);
    }

    public function testQueryExecuteError(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $this->expectException(QueryException::class);
        $e->query('insert into tests(id, name) values (?, ?) ', [4, uniqid()]);
    }

    public function testStartCommitTransaction(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $e->startTransaction();
        $e->exec('insert into tests(name) values (?)', ['TNH']);
        $e->commit();
        $rs = $e->query('select * from tests where name=?', ['TNH']);
        $this->assertEquals('TNH', $rs->column(1));
    }

    public function testStartRollbackTransaction(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $e->startTransaction();
        $e->exec('insert into tests(name) values (?)', ['TNH']);
        $e->rollback();
        $rs = $e->query('select * from tests where name=?', ['TNH']);
        $this->assertFalse($rs->column(1));
    }

    public function testTransaction(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $r = $e->transaction(function ($cnx) {
            return $cnx->exec('insert into tests(name) values (?)', [2]);
        });
        $this->assertEquals(2, $r);
    }

    public function testTransactionError(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $this->expectException(TransactionException::class);
        $e->transaction(function ($cnx) {
             $cnx->getPDO()->exec('insert into tests(id, name) values (1 1)');
        });
    }

    public function testTransactionAlreadyStart(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $e = new Connection($cfg);

        $this->loadTestsData($e->getPDO());

        $e->getPDO()->beginTransaction();

        $r = $e->transaction(function ($cnx) {
             $cnx->getPDO()->exec('insert into tests(name) values (1)');
        });
        $this->assertNull($r);
        $e->getPDO()->rollBack();
    }
}
