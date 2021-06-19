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
use Platine\Database\Exception\ConnectionException;
use Platine\Database\Exception\QueryException;
use Platine\Database\Exception\QueryPrepareException;
use Platine\Database\Exception\TransactionException;
use Platine\Logger\Logger;

/**
 * Class Connection
 * @package Platine\Database
 */
class Connection
{

    /**
     * The PDO instance
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * The PDO data source name
     * @var string
     */
    protected string $dsn = '';

    /**
     * The list of execution query logs
     * @var array<int, array<string, mixed>>
     */
    protected array $logs = [];

    /**
     * The driver to use
     * @var Driver
     */
    protected Driver $driver;

    /**
     * The Schema instance to use
     * @var Schema
     */
    protected Schema $schema;

    /**
     * The connection configuration
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * The connection parameters
     * @var array<int|string, mixed>
     */
    protected array $params = [];

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Connection constructor.
     * @param Configuration $config
     * @param Logger $logger
     * @throws ConnectionException
     */
    public function __construct(
        Configuration $config,
        ?Logger $logger = null
    ) {
        $this->config = $config;

        $this->logger = $logger ?? new Logger();
        $this->logger->setChannel(__CLASS__);

        $driverClass = $this->config->getDriverClassName();
        $this->driver = new $driverClass($this);

        $this->schema = new Schema($this);

        $this->connect();
    }

    /**
     * Connect to the database
     * @return void
     */
    public function connect(): void
    {
        $this->setConnectionParams();

        if ($this->config->isPersistent()) {
            $this->persistent(true);
        }

        $attr = $this->params;

        if (empty($attr)) {
            throw new InvalidArgumentException('Invalid database options supplied');
        }

        $driver = $attr['driver'];
        unset($attr['driver']);

        $params = [];
        foreach ($attr as $key => $value) {
            $params[] = is_int($key) ? $value : $key . '=' . $value;
        }

        $dsn = $driver . ':' . implode(';', $params);
        if (in_array($driver, ['mysql', 'pgsql', 'sqlsrv'])) {
            $charset = $this->config->getCharset();
            $this->config->addCommand('SET NAMES "' . $charset . '"' . (
                    $this->config->getDriverName() === 'mysql'
                            ? ' COLLATE "' . $this->config->getCollation() . '"'
                            : ''
                    ));
        }

        $this->dsn = $dsn;

        try {
            $this->pdo = new PDO(
                $this->dsn,
                $this->config->getUsername(),
                $this->config->getPassword(),
                $this->config->getOptions()
            );

            foreach ($this->config->getCommands() as $command) {
                $this->pdo->exec($command);
            }
        } catch (PDOException $exception) {
            $this->logger->emergency('Can not connect to database. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage()
            ]);

            throw new ConnectionException(
                'Can not connect to database',
                (int) $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

    /**
     *
     * @param Logger $logger
     * @return self
     */
    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Return the query execution logs
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Return the current connection parameters
     * @return array<int|string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Return the current connection configuration
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Return the current driver instance
     * @return Driver
     */
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * Return the current Schema instance
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Set connection to be persistent
     * @param bool $value
     * @return self
     */
    public function persistent(bool $value = true): self
    {
        $this->config->setOption(PDO::ATTR_PERSISTENT, $value);

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
     * Return the instance of the PDO
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute the SQL query and return the result
     * @param string $sql
     * @param array<int, mixed> $params the query parameters
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
     * @param array<int, mixed> $params the query parameters
     * @return bool
     * @throws QueryException
     */
    public function exec(string $sql, array $params = []): bool
    {
        return $this->execute($this->prepare($sql, $params));
    }

    /**
     *  Execute the SQL query and return the number
     * of affected rows
     * @param string $sql
     * @param array<int, mixed> $params the query parameters
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
     * @param array<int, mixed> $params the query parameters
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
            throw new TransactionException(
                $exception->getMessage(),
                (int) $exception->getCode(),
                $exception->getPrevious()
            );
        }

        return $result;
    }

    /**
     * Change the query parameters placeholder with the value
     * @param string $query
     * @param array<int, mixed> $params
     * @return string
     */
    protected function replaceParameters(string $query, array $params): string
    {
        $driver = $this->driver;

        return (string) preg_replace_callback(
            '/\?/',
            function () use ($driver, &$params) {
                $param = array_shift($params);

                $value = is_object($param) ? get_class($param) : $param;
                if (is_int($value) || is_float($value)) {
                    return $value;
                }
                if ($value === null) {
                    return 'NULL';
                }
                if (is_bool($value)) {
                    return $value ? 'TRUE' : 'FALSE';
                }
                return $driver->quote($value);
            },
            $query
        );
    }

    /**
     * Prepare the query
     * @param string $query
     * @param array<mixed> $params
     * @return array<string, mixed>
     * @throws QueryException
     */
    protected function prepare(string $query, array $params): array
    {
        try {
            $statement = $this->pdo->prepare($query);
        } catch (PDOException $exception) {
            $this->logger->error('Error when prepare query [{query}]. Error message: {error}', [
                'exception' => $exception,
                'error' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new QueryPrepareException(
                $exception->getMessage() . ' [' . $query . ']',
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
     * @param array<string, mixed> $prepared
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

            $this->logs[] = $sqlLog;

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
     * @param array<int, mixed> $values
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
     * Set the PDO connection parameters to use
     * @return void
     */
    protected function setConnectionParams(): void
    {
        $port = $this->config->getPort();
        $database = $this->config->getDatabase();
        $hostname = $this->config->getHostname();
        $attr = [];

        $driverName = $this->config->getDriverName();
        switch ($driverName) {
            case 'mysql':
            case 'pgsql':
                $attr = [
                    'driver' => $driverName,
                    'dbname' => $database,
                    'host' => $hostname,
                ];

                if ($port > 0) {
                    $attr['port'] = $port;
                }

                if ($driverName === 'mysql') {
                    //Make MySQL using standard quoted identifier
                    $this->config->addCommand('SET SQL_MODE=ANSI_QUOTES');
                    $this->config->addCommand('SET CHARACTER SET "' . $this->config->getCharset() . '"');

                    $socket = $this->config->getSocket();
                    if (!empty($socket)) {
                        $attr['unix_socket'] = $socket;
                    }
                }
                break;
            case 'sqlsrv':
                //Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                $this->config->addCommand('SET QUOTED_IDENTIFIER ON');

                //Make ANSI_NULLS is ON for NULL value
                $this->config->addCommand('SET ANSI_NULLS ON');

                $attr = [
                    'driver' => 'sqlsrv',
                    'Server' => $hostname
                        . ($port > 0 ? ':' . $port : ''),
                    'Database' => $database
                ];

                $appName = $this->config->getAppname();
                if (!empty($appName)) {
                    $attr['APP'] = $appName;
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
                    $str = preg_replace(
                        ['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'],
                        '$1_$2',
                        $attribute
                    );

                    if (is_string($str)) {
                        $keyname = strtolower($str);

                        if ($this->config->hasAttribute($keyname)) {
                            $attr[$attribute] = $this->config->getAttribute($keyname);
                        }
                    }
                }
                break;
            case 'oci':
            case 'oracle':
                $attr = [
                    'driver' => 'oci',
                    'dbname' => '//' . $hostname
                    . ($port > 0 ? ':' . $port : ':1521') . '/' . $database
                ];

                $attr['charset'] = $this->config->getCharset();
                break;
            case 'sqlite':
                $attr = [
                    'driver' => 'sqlite',
                    $database
                ];
                break;
        }

        $this->params = $attr;
    }
}
