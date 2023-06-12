<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Connection;
use Platine\Database\Driver\MySQL;
use Platine\Database\ResultSet;
use Platine\Database\Schema;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateTable;
use Platine\Test\Fixture\PlatineTestCaseDb;
use stdClass;

/**
 * Schema class tests
 *
 * @group core
 * @group database
 */
class SchemaTest extends PlatineTestCaseDb
{
    public function testConstructor(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $e = new Schema($cnx);

        $sr = $this->getPrivateProtectedAttribute(Schema::class, 'connection');

        $this->assertInstanceOf(Connection::class, $sr->getValue($e));
        $this->assertEquals($cnx, $sr->getValue($e));
    }

    public function testGetDatabaseName(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $e = new Schema($cnx);

        $expected = ':memory:';
        $this->assertEquals($expected, $e->getDatabaseName());
    }

    public function testGetDatabaseNameNotSQLite(): void
    {
        /** @var Connection $cnx */
        $cnx = $this->getMockBuilder(Connection::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        /** @var MySQL $driver */
        $driver = $this->getMockBuilder(MySQL::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $driver->expects($this->once())
                ->method('getDatabaseName')
                ->will($this->returnValue([
                    'sql' => 'SELECT database()',
                    'params' => []
                ]));

         $cnx->expects($this->once())
                ->method('getDriver')
                ->will($this->returnValue($driver));

         $cnx->expects($this->once())
                ->method('column')
                ->will($this->returnValue('test_db'));

        $e = new Schema($cnx);

        $dbname = $e->getDatabaseName();
        $this->assertEquals('test_db', $dbname);
    }

    public function testHasTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $this->assertFalse($e->hasTable('goo'));
        $this->assertTrue($e->hasTable('tests'));
    }

    public function testHasView(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $this->assertFalse($e->hasView('goo'));
        $this->assertTrue($e->hasView('v_tests'));
    }

    public function testGetTables(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $tables = $e->getTables(true);

        //sqlite have also table "sqlite_sequence"
        $this->assertCount(2, $tables);
        $this->assertArrayHasKey('sqlite_sequence', $tables);
        $this->assertArrayHasKey('tests', $tables);
    }

    public function testGetViews(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $tables = $e->getViews(true);

        $this->assertCount(1, $tables);
        $this->assertArrayHasKey('v_tests', $tables);
    }

    public function testGetColumnsTableNotExists(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getColumns('not_found_table', true);
        $this->assertEmpty($columns);
    }

    public function testGetViewColumnsViewNotExists(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getViewColumns('not_found_view', true);
        $this->assertEmpty($columns);
    }

    public function testGetColumnsNamed(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getColumns('tests', true, true);

        $this->assertCount(2, $columns);
        $this->assertEquals('id', $columns[0]);
        $this->assertEquals('name', $columns[1]);
    }

    public function testGetViewColumnsNamed(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getViewColumns('v_tests', true, true);

        $this->assertCount(1, $columns);
        $this->assertEquals('name', $columns[0]);
    }

    public function testGetColumnsNotNamed(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getColumns('tests', true, false);

        $this->assertCount(2, $columns);
        $this->assertArrayHasKey('id', $columns);
        $this->assertArrayHasKey('name', $columns);
    }

    public function testGetViewColumnsNotNamed(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $columns = $e->getViewColumns('v_tests', true, false);

        $this->assertCount(1, $columns);
        $this->assertArrayHasKey('name', $columns);
    }

    public function testCreateTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $table = 'my_table';

        $this->assertFalse($e->hasTable($table));

        $e->create($table, function (CreateTable $t) {
            $t->integer('id')
                    ->autoincrement();
            $t->string('name');
        });

        $this->assertTrue($e->hasTable($table));
        $this->assertCount(2, $e->getColumns($table, true, false));
    }

    public function testAlterTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $table = 'tests';

        $this->assertTrue($e->hasTable($table));
        $this->assertCount(2, $e->getColumns($table, true, false));

        $e->alter($table, function (AlterTable $t) {
            $t->boolean('status');
        });

        $this->assertCount(3, $e->getColumns($table, true, false));
    }

    public function testRenameTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $old_table = 'tests';
        $new_table = 'tests_new';

        $this->assertTrue($e->hasTable($old_table));
        $this->assertFalse($e->hasTable($new_table));

        $e->renameTable($old_table, $new_table);

        $this->assertFalse($e->hasTable($old_table));
        $this->assertTrue($e->hasTable($new_table));
    }

    public function testDropTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $table = 'tests';

        $this->assertTrue($e->hasTable($table));
        $e->drop($table);
        $this->assertFalse($e->hasTable($table));
    }

    public function testTruncateTable(): void
    {
        $cfg = $this->getDbConnectionConfigOK();

        $cnx = new Connection($cfg);

        $this->loadTestsData($cnx->getPDO());

        $e = new Schema($cnx);

        $table = 'tests';
        $res = $cnx->query('select * from tests where id = ?', [1]);
        $this->assertInstanceOf(ResultSet::class, $res);
        $this->assertInstanceOf(stdClass::class, $res->get());

        $e->truncate($table);

        $resNew = $cnx->query('select * from tests where id = ?', [1]);
        $this->assertInstanceOf(ResultSet::class, $resNew);
        $this->assertFalse($resNew->get());
    }
}
