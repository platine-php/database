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
 *  @file Configuration.php
 *
 *  The connection configuration class
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

use PDO;
use Platine\Database\Driver\Driver;
use Platine\Database\Driver\MySQL;
use Platine\Database\Driver\Oracle;
use Platine\Database\Driver\PostgreSQL;
use Platine\Database\Driver\SQLite;
use Platine\Database\Driver\SQLServer;

/**
 * Class Configuration
 * @package Platine\Database
 */
class Configuration implements ConfigurationInterface
{
    /**
     * The connection driver to use
     * @var string
     */
    protected string $driver = 'mysql';

    /**
     * The connection name
     * @var string
     */
    protected string $name = 'default';

    /**
     * The driver character set
     * Only for some drivers
     * @var string
     */
    protected string $charset = 'UTF8';

    /**
     * The application name
     * Only for Microsoft SQL server
     * @var string
     */
    protected string $appname = '';

    /**
     * The connection host name
     * @var string
     */
    protected string $hostname = 'localhost';

    /**
     * The connection username
     * @var string
     */
    protected string $username = '';

    /**
     * The connection username
     * @var string
     */
    protected string $password = '';

    /**
     * The connection port. If null will use the standard
     * port for database server
     * @var int|null
     */
    protected ?int $port = null;

    /**
     * The connection database name to use
     * Note: for SQLite this is the path to the database file
     * @var string
     */
    protected string $database = '';

    /**
     * The database server collation to use
     * Only for MySQL
     * @var string
     */
    protected string $collation = 'utf8_general_ci';

    /**
     * The connection socket to use for if the driver is MySQL
     * @var string
     */
    protected string $socket = '';

    /**
     * Whether the connection is persistent
     * @var bool
     */
    protected bool $persistent = false;


    /**
     * The PDO connection options
     * @var array<mixed, mixed>
     */
    protected array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * The connection attributes to customize some drivers
     * @var array<mixed, mixed>
     */
    protected array $attributes = [];

    /**
     * The list of SQL command to execute after connection
     * @var array<int, string>
     */
    protected array $commands = [];

    /**
     * Class constructor
     * @param array<string, mixed> $config the connection configuration
     */
    public function __construct(array $config = [])
    {
        $this->load($config);
    }

    /**
     * Return the driver name
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driver;
    }

    /**
     * Return the name of the configuration connection
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the charset
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     *  Return the application name
     * @return string
     */
    public function getAppname(): string
    {
        return $this->appname;
    }

     /**
     *  Return the hostname
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     *
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     *
     * @return string
     */
    public function getCollation(): string
    {
        return $this->collation;
    }

    /**
     *
     * @return string
     */
    public function getSocket(): string
    {
        return $this->socket;
    }

    /**
     *
     * @return array<mixed, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the PDO connection option
     * @param mixed $name
     * @param mixed $value
     * @return $this
     */
    public function setOption($name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set an array of options
     * @param array<mixed, mixed> $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the connection attribute
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Set an array of attributes
     * @param array<string, mixed> $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Check whether the attribute exist
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->hasAttribute($name)
                       ? $this->attributes[$name]
                       : $default;
    }

    /**
     *
     * @return array<int, mixed>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Add the connection command
     * @param string $command
     * @return $this
     */
    public function addCommand(string $command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Add an array of commands
     * @param array<int, string> $commands
     * @return $this
     */
    public function addCommands(array $commands): self
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * Load the database configuration from array
     * @param array<string, mixed> $config
     *
     * @example
     * array (
     *        'name' => 'default',
     *        'driver' => 'mysql',
     *        'database' => 'db_name',
     *        'hostname' => '127.0.0.1',
     *        'port' => xxxx,
     *        'username' => 'usrname',
     *        'password' => '',
     *        'persistent' => true,
     * );
     *
     * @return void
     */
    public function load(array $config): void
    {
        foreach ($config as $name => $value) {
            $key = str_replace('_', '', lcfirst(ucwords($name, '_')));
            if (property_exists($this, $key)) {
                if (in_array($key, ['options', 'attributes', 'commands']) && is_array($value)) {
                    $method = 'set' . ucfirst($key);
                    if ($key === 'commands') {
                        $method = 'addCommands';
                    }
                    $this->{$method}($value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Return the connection driver class name
     * @return string
     */
    public function getDriverClassName(): string
    {
        $maps = [
          'mysql'  => MySQL::class,
          'pgsql'  => PostgreSQL::class,
          'sqlsrv' => SQLServer::class,
          'oci'    => Oracle::class,
          'oracle' => Oracle::class,
          'sqlite' => SQLite::class,
        ];

        return isset($maps[$this->driver])
                ? $maps[$this->driver]
                : Driver::class;
    }

    /**
     * Whether the connection is persistent
     * @return bool
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }
}
