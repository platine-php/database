<?php

/**
 * Platine Database
 *
 * Platine Database is the abstraction layer using PDO with support of query and schema builder
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Database
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file Connection.php
 *
 *  The Database Connection class
 *
 *  @package    Platine\Database
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */
declare(strict_types=1);

namespace Platine\Database;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use Platine\Database\Driver\Driver;
use Platine\Database\Driver\MySQL;
use Platine\Database\Driver\Oracle;
use Platine\Database\Driver\PostgreSQL;
use Platine\Database\Driver\SQLite;
use Platine\Database\Driver\SQLServer;
use Platine\Database\Exception\ConnectionException;
use Platine\Database\Exception\QueryException;
use Platine\Logger\Logger;
use Platine\Logger\NullLogger;

/**
 * Class Connection
 * @package Platine\Database
 */
class Connection
{

    /**
     * The PDO instance
     * @var PDO|null
     */
    protected ?PDO $pdo;

    /**
     * The database driver name to use
     * @var string
     */
    protected string $driverName = '';

    /**
     * The PDO dsn
     * @var string
     */
    protected string $dsn = '';

    /**
     * The PDO connection options
     * @var array
     */
    protected array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * The list of SQL command to execute after connection
     * @var array
     */
    protected array $commands = [];

    /**
     * The driver to use
     * @var Driver|null
     */
    protected ?Driver $driver = null;

    /**
     * The Schema instance to use
     * @var Schema|null
     */
    protected ?Schema $schema = null;

    /**
     * The driver options
     * @var array
     */
    protected array $driverOptions = [];

    /**
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * Connection constructor.
     * @param array $config
     * @param Logger $logger
     * @throws ConnectionException
     */
    public function __construct(array $config = [], ?Logger $logger = null)
    {
        $this->logger = $logger ? $logger : new Logger(new NullLogger());
        
        $defaultConfig = [
            'driver' => 'mysql',
            'charset' => 'UTF8', //only for some drivers, 
            'appname' => '', //only for MSSQL, DBLIB, 
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'port' => null,
            'database' => '',
            'collation' => 'utf8_general_ci', //only for MySQL
            'socket' => '', //only for MySQL
            'options' => [],
            'commands' => [],
        ];
        
        $dbConfig = array_merge($defaultConfig, $config);
        
        if (is_string($dbConfig['driver'])) {
            $this->driverName = strtolower($dbConfig['driver']);
        }

        $options = $this->options;
        if (is_array($dbConfig['options'])) {
            $options = array_merge($options, $dbConfig['options']);
        }

        $commands = $this->commands;
        if (is_array($dbConfig['commands'])) {
            $commands = array_merge($commands, $dbConfig['commands']);
        }

        $port = null;
        $attr = [];

        if (is_int($dbConfig['port'])) {
            $port = $dbConfig['port'];
        }
        
        $driverName = $this->driverName;
        switch ($driverName) {
            case 'mysql':
            case 'pgsql':
                $attr = [
                    'driver' => $driverName,
                    'dbname' => $dbConfig['database'],
                    'host' => $dbConfig['hostname'],
                ];
                
                if ($port > 0) {
                    $attr['port'] = $port;
                }
                
                if ($driverName === 'mysql'){
                    //Make MySQL using standard quoted identifier
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    
                    if (!empty($dbConfig['socket'])) {
                        $attr['unix_socket'] = $dbConfig['socket'];
                        
                        unset($attr['host']);
                        unset($attr['port']);
                    } 
                }
                break;
            case 'sqlsrv':
                //Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                $commands[] = 'SET QUOTED_IDENTIFIER ON';

                //Make ANSI_NULLS is ON for NULL value
                $commands[] = 'SET ANSI_NULLS ON';
                
                $attr = [
                    'driver' => 'sqlsrv',
                    'Server' => $dbConfig['hostname'] 
                        . ($port > 0 ? ':' . $port : ''),
                    'Database' => $dbConfig['database']
                ];

                if (!empty($dbConfig['appname'])) {
                    $attr['APP'] = $dbConfig['appname'];
                }

                $attributes = [
                    'ApplicationIntent',
                    'AttachDBFileName',
                    'Authentication',
                    'ColumnEncryption',
                    'ConnectionPooling',
                    'Encrypt',
                    'Failover_Partner',
                    'KeyStoreAuthentication',
                    'KeyStorePrincipalId',
                    'KeyStoreSecret',
                    'LoginTimeout',
                    'MultipleActiveResultSets',
                    'MultiSubnetFailover',
                    'Scrollable',
                    'TraceFile',
                    'TraceOn',
                    'TransactionIsolation',
                    'TransparentNetworkIPResolution',
                    'TrustServerCertificate',
                    'WSID',
                ];

                foreach ($attributes as $attribute) {
                    $keyname = strtolower(preg_replace(
                        ['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'],
                        '$1_$2',
                        $attribute
                    ));

                    if (isset($dbConfig[$keyname])) {
                        $attr[$attribute] = $dbConfig[$keyname];
                    }
                }
                break;
            case 'oci':
            case 'oracle':
                $database = $dbConfig['database'];
                $attr = [
                    'driver' => 'oci',
                    'dbname' => '//' . $dbConfig['hostname']
                    . ($port > 0 ? ':' . $port : ':1521') . '/' . $database
                ];
                
                $attr['charset'] = $dbConfig['charset'];
                break;
            case 'sqlite':
                $attr = [
                    'driver' => 'sqlite',
                    $dbConfig['database']
                ];
                break;
        }
        

        if (empty($attr)) {
            throw new InvalidArgumentException('Invalid database options supplied');
        }

        $driver = $attr['driver'];
        if (!in_array($driver, PDO::getAvailableDrivers())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid database driver [%s], must be one of [%s]',
                $driver,
                implode(', ', PDO::getAvailableDrivers())
            ));
        }

        unset($attr['driver']);

        $params = [];
        foreach ($attr as $key => $value) {
            $params[] = is_int($key) ? $value : $key . '=' . $value;
        }

        $dsn = $driver . ':' . implode(';', $params);
        if (in_array($driver, ['mysql', 'pgsql', 'sqlsrv'])) {
            $commands[] = 'SET NAMES "' . $dbConfig['charset'] . '"' . (
                    $this->driverName === 'mysql' 
                            ? ' COLLATE "' . $dbConfig['collation'] . '"' 
                            : ''
                    );
        }

        $this->dsn = $dsn;
        $this->commands = $commands;
        $this->options = $options;

        try {
            $this->pdo = new PDO(
                $dsn,
                isset($config['username']) ? $config['username'] : '',
                isset($config['password']) ? $config['password'] : '',
                $options
            );

            foreach ($commands as $command) {
                $this->pdo->exec($command);
            }
        } catch (PDOException $exception) {
            $this->logger->emergency('Can not connect to database. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage()
            ]);
            throw new ConnectionException($exception->getMessage());
        }
    }

    /**
     * @param Logger $logger
     * @return self
     */
    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Return the current driver instance
     * @return Driver
     */
    public function getDriver(): Driver
    {
        if ($this->driver === null) {
            $this->setDefaultDriver();
        }
        return $this->driver;
    }

    /**
     * @param Driver $driver
     * @return self
     */
    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Return the current Schema instance
     * @return Schema
     */
    public function getSchema(): Schema
    {
        if ($this->schema === null) {
            $this->schema = new Schema($this);
        }
        return $this->schema;
    }

    /**
     * @param Schema $schema
     * @return self
     */
    public function setSchema(Schema $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Set PDO Connection options
     * @param array $options
     * @return self
     */
    public function options(array $options): self
    {
        foreach ($options as $name => $value) {
            $this->option($name, $value);
        }

        return $this;
    }

    /**
     * Set the PDO connection option
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function option(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set connection to be persistent
     * @param bool $value
     * @return self
     */
    public function persistent(bool $value = true): self
    {
        $this->pdo->setAttribute(PDO::ATTR_PERSISTENT, $value);

        return $this;
    }

    /**
     * Set the date format to use for the current driver
     * @param string $format
     * @return self
     */
    public function setDateFormat(string $format): self
    {
        $this->driverOptions['dateFormat'] = $format;

        return $this;
    }

    /**
     * Set the quote identifier to use for the current driver
     * @param string $identifier
     * @return self
     */
    public function setQuoteIdentifier(string $identifier): self
    {
        $this->driverOptions['identifier'] = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * Return the name of the connection driver
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driverName;
    }

    /**
     * Return the instance of the PDO
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * CLose the connection
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Execute the SQL query and return the result
     * @param string $sql
     * @param array $params the query parameters
     * @return ResultSet
     * @throws QueryException
     */
    public function query(string $sql, array $params = []): ResultSet
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);

        return new ResultSet($prepared['statement']);
    }

    /**
     * Direct execute the SQL query
     * @param string $sql
     * @param array $params the query parameters
     * @return mixed
     * @throws QueryException
     */
    public function exec(string $sql, array $params = [])
    {
        return $this->execute($this->prepare($sql, $params));
    }

    /**
     *  Execute the SQL query and return the number
     * of affected rows
     * @param string $sql
     * @param array $params the query parameters
     * @return int
     * @throws QueryException
     */
    public function count(string $sql, array $params = []): int
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);

        $result = $prepared['statement']->rowCount();
        $prepared['statement']->closeCursor();

        return $result;
    }

    /**
     *  Execute the SQL query and return the first column result
     * @param string $sql
     * @param array $params the query parameters
     * @return mixed
     * @throws QueryException
     */
    public function column(string $sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);

        $result = $prepared['statement']->fetchColumn();
        $prepared['statement']->closeCursor();

        return $result;
    }

    /**
     * @param callable $callback
     * @param mixed|null $that
     *
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function transaction(
        callable $callback,
        $that = null
    ) {
        if ($that === null) {
            $that = $this;
        }

        if ($this->pdo->inTransaction()) {
            return $callback($that);
        }

        try {
            $this->pdo->beginTransaction();
            $result = $callback($that);
            $this->pdo->commit();
        } catch (PDOException $exception) {
            $this->pdo->rollBack();
            $this->logger->error('Database transaction error. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage()
            ]);
            throw new ConnectionException($exception->getMessage());
        }

        return $result;
    }

    /**
     * Change the query parameters placeholder with the value
     * @param string $query
     * @param array $params
     * @return string
     */
    protected function replaceParameters(string $query, array $params): string
    {
        $driver = $this->getDriver();

        return preg_replace_callback(
            '/\?/',
            function () use ($driver, &$params) {
                $param = array_shift($params);
                $param = is_object($param) ? get_class($param) : $param;
                if (is_int($param) || is_float($param)) {
                    return $param;
                }
                if ($param === null) {
                    return 'NULL';
                }
                if (is_bool($param)) {
                    return $param ? 'TRUE' : 'FALSE';
                }
                return $driver->quote($param);
            },
            $query
        );
    }

    /**
     * Prepare the query
     * @param string $query
     * @param array $params
     * @return array
     * @throws QueryException
     */
    protected function prepare(string $query, array $params): array
    {
        try {
            $statement = $this->pdo->prepare($query);
        } catch (PDOException $exception) {
            $sql = $this->replaceParameters($query, $params);
            $this->logger->error('Error when prepare query [{sql}]. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage(),
                'sql' => $sql
            ]);
            throw new QueryException(
                $exception->getMessage() . ' [' . $sql . ']',
                (int) $exception->getCode(),
                $exception->getPrevious()
            );
        }

        return [
            'statement' => $statement,
            'query' => $query,
            'params' => $params
        ];
    }

    /**
     * Execute the prepared query
     * @param array $prepared
     * @return bool the status of the execution
     * @throws QueryException
     */
    protected function execute(array $prepared): bool
    {
        $sql = $this->replaceParameters($prepared['query'], $prepared['params']);
        $sqlLog = [
            'query' => $prepared['query'],
            'parameters' => implode(', ', $prepared['params'])
        ];

        try {
            if ($prepared['params']) {
                $this->bindValues($prepared['statement'], $prepared['params']);
            }
            $start = microtime(true);
            $result = $prepared['statement']->execute();
            $sqlLog['time'] = number_format(microtime(true) - $start, 6);

            $this->logger->info(
                'Execute Query: [{query}], parameters: [{parameters}], time: [{time}]',
                $sqlLog
            );
        } catch (PDOException $exception) {
            $this->logger->error('Error when execute query [{sql}]. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage(),
                'sql' => $sql
            ]);
            throw new QueryException(
                $exception->getMessage() . ' [' . $sql . ']',
                (int) $exception->getCode(),
                $exception->getPrevious()
            );
        }

        return $result;
    }

    /**
     * Bind the parameters values
     * @param PDOStatement $statement
     * @param array $values
     */
    protected function bindValues(PDOStatement $statement, array $values): void
    {
        foreach ($values as $key => $value) {
            $param = PDO::PARAM_STR;
            if (is_null($value)) {
                $param = PDO::PARAM_NULL;
            } elseif (is_int($value) || is_float($value)) {
                $param = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $param = PDO::PARAM_BOOL;
            }

            $statement->bindValue($key + 1, $value, $param);
        }
    }

    /**
     * Set the default driver instance using current driver name
     * @return void
     */
    protected function setDefaultDriver(): void
    {
        switch ($this->driverName) {
            case 'mysql':
                $this->driver = new MySQL($this);
                break;
            case 'pgsql':
                $this->driver = new PostgreSQL($this);
                break;
            case 'dblib':
            case 'mssql':
            case 'sqlsrv':
            case 'sybase':
                $this->driver = new SQLServer($this);
                break;
            case 'oci':
            case 'oracle':
                $this->driver = new Oracle($this);
                break;
            case 'sqlite':
                $this->driver = new SQLite($this);
                break;
            default:
                $this->driver = new Driver($this);
        }
        $this->driver->setOptions($this->driverOptions);
    }
}
