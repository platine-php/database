<?php

declare(strict_types=1);

namespace Platine\Test\Database;

use Platine\Database\Driver\MySQL;
use Platine\Database\Schema\AlterColumn;
use Platine\Database\Schema\AlterTable;
use Platine\Database\Schema\CreateColumn;
use Platine\Database\Schema\CreateTable;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Connection;
use Platine\Test\Fixture\Schema;

/**
 * MySQL class tests
 *
 * @group core
 * @group database
 */
class MySQLTest extends PlatineTestCase
{
    public function testCreateAllDataTypes(): void
    {

        $e = $this->getMySQLInstance();

        //Integer
        $ccIntMockMethodsMaps = [
            'getName' => 'col_int',
            'getType' => 'integer',
        ];

        $ccMedMockMethodsMaps = [
            'getName' => 'col_medium_int',
            'getType' => 'integer',
        ];

        $ccTinyMockMethodsMaps = [
            'getName' => 'col_tiny_int',
            'getType' => 'integer',
        ];

        $ccSmallMockMethodsMaps = [
            'getName' => 'col_small_int',
            'getType' => 'integer',
        ];

        $ccBigMockMethodsMaps = [
            'getName' => 'col_big_int',
            'getType' => 'integer',
        ];

        //Text
        $ccTextMockMethodsMaps = [
            'getName' => 'col_text',
            'getType' => 'text',
        ];

        $ccTextMedMockMethodsMaps = [
            'getName' => 'col_medium_text',
            'getType' => 'text',
        ];

        $ccTextTinyMockMethodsMaps = [
            'getName' => 'col_tiny_text',
            'getType' => 'text',
        ];

        $ccTextSmallMockMethodsMaps = [
            'getName' => 'col_small_text',
            'getType' => 'text',
        ];

        $ccTextBigMockMethodsMaps = [
            'getName' => 'col_big_text',
            'getType' => 'text',
        ];

        //Decimal
        $ccDecSimpleMockMethodsMaps = [
            'getName' => 'col_dec_sim',
            'getType' => 'decimal',
        ];

        $ccDecLenMockMethodsMaps = [
            'getName' => 'col_dec_len',
            'getType' => 'decimal',
        ];

        $ccDecPreMockMethodsMaps = [
            'getName' => 'col_dec_pre',
            'getType' => 'decimal',
        ];

        //Boolean
        $ccBoolMockMethodsMaps = [
            'getName' => 'col_bool',
            'getType' => 'boolean',
        ];


        //Binary
        $ccBinaryMockMethodsMaps = [
            'getName' => 'col_binary',
            'getType' => 'binary',
        ];

        $ccBinaryMedMockMethodsMaps = [
            'getName' => 'col_medium_binary',
            'getType' => 'binary',
        ];

        $ccBinaryTinyMockMethodsMaps = [
            'getName' => 'col_tiny_binary',
            'getType' => 'binary',
        ];

        $ccBinarySmallMockMethodsMaps = [
            'getName' => 'col_small_binary',
            'getType' => 'binary',
        ];

        $ccBinaryBigMockMethodsMaps = [
            'getName' => 'col_big_binary',
            'getType' => 'binary',
        ];

        //Integer
        $int = $this->getCreateColumnInstance($ccIntMockMethodsMaps);
        $int->set('size', 'normal');
        $int->set('description', 'simple int column');

        $tiny = $this->getCreateColumnInstance($ccTinyMockMethodsMaps);
        $tiny->set('size', 'tiny');

        $small = $this->getCreateColumnInstance($ccSmallMockMethodsMaps);
        $small->set('size', 'small');

        $medium = $this->getCreateColumnInstance($ccMedMockMethodsMaps);
        $medium->set('size', 'medium');

        $big = $this->getCreateColumnInstance($ccBigMockMethodsMaps);
        $big->set('size', 'big');

        //Text
        $text = $this->getCreateColumnInstance($ccTextMockMethodsMaps);
        $text->set('size', 'normal');

        $textTiny = $this->getCreateColumnInstance($ccTextTinyMockMethodsMaps);
        $textTiny->set('size', 'tiny');

        $textSmall = $this->getCreateColumnInstance($ccTextSmallMockMethodsMaps);
        $textSmall->set('size', 'small');

        $textMedium = $this->getCreateColumnInstance($ccTextMedMockMethodsMaps);
        $textMedium->set('size', 'medium');

        $textBig = $this->getCreateColumnInstance($ccTextBigMockMethodsMaps);
        $textBig->set('size', 'big');

        //Boolean
        $bool = $this->getCreateColumnInstance($ccBoolMockMethodsMaps);

        //Decimal
        $decSimple = $this->getCreateColumnInstance($ccDecSimpleMockMethodsMaps);

        $decLength = $this->getCreateColumnInstance($ccDecLenMockMethodsMaps);
        $decLength->set('length', 10);

        $decPrecision = $this->getCreateColumnInstance($ccDecPreMockMethodsMaps);
        $decPrecision->set('length', 10);
        $decPrecision->set('precision', 2);

        //Binary
        $binary = $this->getCreateColumnInstance($ccBinaryMockMethodsMaps);
        $binary->set('size', 'normal');

        $binaryTiny = $this->getCreateColumnInstance($ccBinaryTinyMockMethodsMaps);
        $binaryTiny->set('size', 'tiny');

        $binarySmall = $this->getCreateColumnInstance($ccBinarySmallMockMethodsMaps);
        $binarySmall->set('size', 'small');

        $binaryMedium = $this->getCreateColumnInstance($ccBinaryMedMockMethodsMaps);
        $binaryMedium->set('size', 'medium');

        $binaryBig = $this->getCreateColumnInstance($ccBinaryBigMockMethodsMaps);
        $binaryBig->set('size', 'big');

        //Enum
        $ccEnumMockMethodsMaps = [
            'getName' => 'col_enum',
            'getType' => 'enum',
        ];
        $enum = $this->getCreateColumnInstance($ccEnumMockMethodsMaps);
        $enum->set('values', ['Y', 'N']);

        $columns = [
            'col_int' => $int,
            'col_tiny_int' => $tiny,
            'col_small_int' => $small,
            'col_medium_int' => $medium,
            'col_big_int' => $big,

            'col_text' => $text,
            'col_tiny_text' => $textTiny,
            'col_small_text' => $textSmall,
            'col_medium_text' => $textMedium,
            'col_big_text' => $textBig,

            'col_binary' => $binary,
            'col_tiny_binary' => $binaryTiny,
            'col_small_binary' => $binarySmall,
            'col_medium_binary' => $binaryMedium,
            'col_big_binary' => $binaryBig,

            'col_dec_sim' => $decSimple,
            'col_dec_len' => $decLength,
            'col_dec_pre' => $decPrecision,

            'col_bool' => $bool,
            'col_enum' => $enum,
        ];

        $ctMockMethodsMaps = [
            'getTableName' => 'bar',
            'getColumns' => $columns,
            'getIndexes' => [],
            'getForeignKeys' => [],
            'getUniqueKeys' => [],
            'getPrimaryKey' => [],
        ];

        $ct = $this->getCreateTableInstance($ctMockMethodsMaps);

        $infos = $e->create($ct);

        $this->assertCount(1, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'CREATE TABLE `bar`(
`col_int` INT COMMENT \'simple int column\',
`col_tiny_int` TINYINT,
`col_small_int` SMALLINT,
`col_medium_int` MEDIUMINT,
`col_big_int` BIGINT,
`col_text` TEXT,
`col_tiny_text` TINYTEXT,
`col_small_text` TINYTEXT,
`col_medium_text` MEDIUMTEXT,
`col_big_text` LONGTEXT,
`col_binary` BLOB,
`col_tiny_binary` TINYBLOB,
`col_small_binary` TINYBLOB,
`col_medium_binary` MEDIUMBLOB,
`col_big_binary` LONGBLOB,
`col_dec_sim` DECIMAL,
`col_dec_len` DECIMAL(10),
`col_dec_pre` DECIMAL(10, 2),
`col_bool` TINYINT(1),
`col_enum` ENUM(\'Y\',\'N\'))
';

        $this->assertCommandOutput($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }

    public function testAlterDropKeyAndDefaultSetDrop(): void
    {

        $e = $this->getMySQLInstance();

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'dropDefaultValue',
                    'data' => 'baz'
                ],
                [
                    'type' => 'dropPrimaryKey',
                    'data' => 'baz_pk_col'
                ],
                [
                    'type' => 'dropForeignKey',
                    'data' => 'baz_fk_col'
                ],
                [
                    'type' => 'dropUniqueKey',
                    'data' => 'baz_uk_col'
                ],
                [
                    'type' => 'dropIndex',
                    'data' => 'baz_ik_col'
                ],
                [
                    'type' => 'setDefaultValue',
                    'data' => [
                        'column' => 'bar',
                        'value' => 100,
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);

        $this->assertCount(6, $infos);
        $this->assertIsArray($infos[0]);
        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);
        $this->assertArrayHasKey('sql', $infos[1]);
        $this->assertArrayHasKey('params', $infos[1]);
        $this->assertArrayHasKey('sql', $infos[2]);
        $this->assertArrayHasKey('params', $infos[2]);
        $this->assertArrayHasKey('sql', $infos[3]);
        $this->assertArrayHasKey('params', $infos[3]);
        $this->assertArrayHasKey('sql', $infos[4]);
        $this->assertArrayHasKey('params', $infos[4]);
        $this->assertArrayHasKey('sql', $infos[5]);
        $this->assertArrayHasKey('params', $infos[5]);


        $expectedDropDefaultSql = 'ALTER TABLE `foo` ALTER `baz` DROP DEFAULT';
        $expectedDropPkSql = 'ALTER TABLE `foo` DROP PRIMARY KEY';
        $expectedDropIkSql = 'ALTER TABLE `foo` DROP INDEX `baz_ik_col`';
        $expectedDropFkSql = 'ALTER TABLE `foo` DROP FOREIGN KEY `baz_fk_col`';
        $expectedDropUkSql = 'ALTER TABLE `foo` DROP INDEX `baz_uk_col`';
        $expectedSetDefaultIntSql = 'ALTER TABLE `foo` ALTER `bar` SET DEFAULT 100';

        $this->assertEquals($expectedDropDefaultSql, $infos[0]['sql']);
        $this->assertEquals($expectedDropPkSql, $infos[1]['sql']);
        $this->assertEquals($expectedDropFkSql, $infos[2]['sql']);
        $this->assertEquals($expectedDropUkSql, $infos[3]['sql']);
        $this->assertEquals($expectedDropIkSql, $infos[4]['sql']);
        $this->assertEquals($expectedSetDefaultIntSql, $infos[5]['sql']);

        $this->assertEmpty($infos[0]['params']);
        $this->assertEmpty($infos[1]['params']);
        $this->assertEmpty($infos[2]['params']);
        $this->assertEmpty($infos[3]['params']);
        $this->assertEmpty($infos[4]['params']);
        $this->assertEmpty($infos[5]['params']);
    }

    public function testRenameColumn(): void
    {

        $sch = $this->getMockBuilder(Schema::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $sch->expects($this->any())
             ->method('getColumns')
             ->will($this->returnValue([
                 'old_col' => [
                     'name' => 'old_col',
                     'type' => 'tinyint',
                 ]
             ]));

        $cnx = $this->getMockBuilder(Connection::class)
                      ->disableOriginalConstructor()
                      ->getMock();

        $cnx->expects($this->any())
             ->method('getSchema')
             ->will($this->returnValue($sch));

        $e = new MySQL($cnx);

        $from = 'old_col';
        $to = 'new_col';

        $acIntMockMethodsMaps = [
            'getName' => $to,
            'getType' => 'integer',
        ];

        $atMockMethodsMaps = [
            'getCommands' => [
                [
                    'type' => 'renameColumn',
                    'data' => [
                        'from' => $from,
                        'column' => $this->getAlterColumnInstance($acIntMockMethodsMaps),
                    ]
                ],
            ],
            'getTableName' => 'foo'
        ];

        $at = $this->getAlterTableInstance($atMockMethodsMaps);

        $infos = $e->alter($at);


        $this->assertCount(1, $infos);
        $this->assertIsArray($infos[0]);

        $this->assertArrayHasKey('sql', $infos[0]);
        $this->assertArrayHasKey('params', $infos[0]);

        $expectedSql = 'ALTER TABLE `foo` CHANGE `old_col` `new_col` tinyint';
        $this->assertEquals($expectedSql, $infos[0]['sql']);
        $this->assertEmpty($infos[0]['params']);
    }


    private function getMySQLInstance(): MySQL
    {
        $cnx = new Connection('MySQL');

        return new MySQL($cnx);
    }

    private function getCreateTableInstance(array $mockInfos = []): CreateTable
    {
        /** @var CreateTable $ct */
        $ct = $this->getMockBuilder(CreateTable::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $ct->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $ct;
    }

    private function getAlterTableInstance(array $mockInfos = []): AlterTable
    {
        /** @var AlterTable $at */
        $at = $this->getMockBuilder(AlterTable::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $at->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $at;
    }

    private function getCreateColumnInstance(array $mockInfos = []): CreateColumn
    {
        $methods = $this->getClassMethodsToMock(CreateColumn::class, ['get', 'set']);

        /** @var CreateColumn $cc */
        $cc = $this->getMockBuilder(CreateColumn::class)
                    ->onlyMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $cc->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $cc;
    }

    private function getAlterColumnInstance(array $mockInfos = []): AlterColumn
    {
        $methods = $this->getClassMethodsToMock(AlterColumn::class, ['get', 'set']);

        /** @var AlterColumn $ac */
        $ac = $this->getMockBuilder(AlterColumn::class)
                    ->onlyMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();

        foreach ($mockInfos as $method => $returnValue) {
            $ac->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $ac;
    }
}
