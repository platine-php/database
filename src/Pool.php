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
 *  @file Pool.php
 *
 *  The Database Connection pool class
 *
 *  @package    Platine\Database
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */
declare(strict_types=1);

namespace Platine\Database;

use Platine\Database\Exception\ConnectionAlreadyExistsException;
use Platine\Database\Exception\ConnectionNotFoundException;
use Platine\Logger\Logger;

/**
 * @class Pool
 * @package Platine\Database
 */
class Pool
{
    /**
     * The default connection name
     * @var string
     */
    protected string $default = 'default';

    /**
     * The list of connections
     * @var array<string, array<string, mixed>>
     */
    protected array $connections = [];

    /**
     * Class constructor
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        if (count($config) > 0) {
            if (
                is_array($config['connections']) &&
                count($config['connections']) > 0
            ) {
                 /** @var array<string, array<string, mixed>> $connections */
                $connections = $config['connections'];

                foreach ($connections as $name => $connection) {
                    $connection['name'] = $name;
                    $this->addConnection($connection);
                }
            }

            if (isset($config['default'])) {
                $this->setDefault($config['default']);
            }
        }
    }

    /**
     * Add new connection to the pool
     * @param array<string, mixed> $config
     * @return void
     * @throws ConnectionAlreadyExistsException
     */
    public function addConnection(array $config): void
    {
        /** @var Configuration $cfg */
        $cfg = new Configuration($config);

        $name = $cfg->getName();

        if ($this->has($name)) {
            throw new ConnectionAlreadyExistsException(
                sprintf('The connection [%s] already exists', $name)
            );
        }

        $actives = count($this->connections);

        $this->storeConnectionInfos($cfg);

        if ($actives === 0) {
            $this->default = $name;
        }
    }

    /**
     * Check whether the connection exists
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get the connection instance for the given name
     * if $name is null the default will be returned
     * @param string|null $name
     * @param Logger|null $logger
     * @return Connection
     * @throws ConnectionNotFoundException
     */
    public function getConnection(
        ?string $name = null,
        ?Logger $logger = null
    ): Connection {
        if ($name === null) {
            $name = $this->default;
        }

        $this->checkConnectionName($name);

        $connection = $this->createConnection($name, $logger);

        return $connection;
    }

    /**
     * Set the default connection to use
     * @param string $name
     * @return void
     * @throws ConnectionNotFoundException
     */
    public function setDefault(string $name): void
    {
        $this->checkConnectionName($name);

        $this->default = $name;
    }


    /**
     * Remove the given connection
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->connections[$name]);
    }

    /**
     *
     * @param string $name
     * @return void
     * @throws ConnectionNotFoundException
     */
    protected function checkConnectionName(string $name): void
    {
        if ($this->has($name) === false) {
            throw new ConnectionNotFoundException(
                sprintf('The connection [%s] does not exist', $name)
            );
        }
    }

    /**
     * Store the connection information
     * @param Configuration $config
     * @return void
     */
    protected function storeConnectionInfos(Configuration $config): void
    {
        $name = $config->getName();

        $this->connections[$name] = [
            'config' => $config,
            'instance' => null
        ];
    }

    /**
     * Create the connection
     * @param string $name
     * @param Logger|null $logger
     * @return Connection
     */
    protected function createConnection(string $name, ?Logger $logger = null): Connection
    {
        /** @var array<string, mixed>|null $infos */
        $infos = $this->connections[$name];

        if (is_array($infos)) {
            if (is_null($infos['instance'])) {
                $this->connections[$name]['instance'] = new Connection(
                    $infos['config'],
                    $logger
                );
            }
        }

        return $this->connections[$name]['instance'];
    }
}
