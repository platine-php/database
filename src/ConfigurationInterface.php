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
 *  @file ConfigurationInterface.php
 *
 *  The connection configuration interface
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

/**
 * Interface ConfigurationInterface
 * @package Platine\Database
 */
interface ConfigurationInterface
{

    /**
     *
     * @return string
     */
    public function getDriverName(): string;

    /**
     * Return the name of the configuration connection
     * @return string
     */
    public function getName(): string;

    /**
     *
     * @return string
     */
    public function getCharset(): string;

    /**
     *
     * @return string
     */
    public function getAppname(): string;

    /**
     *
     * @return string
     */
    public function getHostname(): string;

    /**
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     *
     * @return int|null
     */
    public function getPort(): ?int;

    /**
     *
     * @return string
     */
    public function getDatabase(): string;

    /**
     *
     * @return string
     */
    public function getCollation(): string;

    /**
     *
     * @return string
     */
    public function getSocket(): string;

    /**
     *
     * @return array<mixed, mixed>
     */
    public function getOptions(): array;

    /**
     * Set the PDO connection option
     * @param mixed $name
     * @param mixed $value
     * @return self
     */
    public function setOption($name, $value): self;

    /**
     *
     * @return array<mixed, mixed>
     */
    public function getAttributes(): array;

    /**
     * Set the connection attribute
     * @param mixed $name
     * @param mixed $value
     * @return self
     */
    public function setAttribute($name, $value): self;

    /**
     * Check whether the attribute exist
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool;

    /**
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $name, $default = null);

    /**
     *
     * @return array<int, mixed>
     */
    public function getCommands(): array;

    /**
     * Add the connection command
     * @param string $command
     * @return self
     */
    public function addCommand(string $command): self;

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
    public function load(array $config): void;

    /**
     * Return the connection driver class name
     * @return string
     */
    public function getDriverClassName(): string;

    /**
     * Whether the connection is persistent
     * @return bool
     */
    public function isPersistent(): bool;
}
