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
 *  @file ResultSet.php
 *
 *  The database Result Set class
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

use Closure;
use PDO;
use PDOStatement;

/**
 * Class ResultSet
 * @package Platine\Database
 */
class ResultSet
{
    /**
     * The PDOStatement instance
     * @var PDOStatement
     */
    protected PDOStatement $statement;

    /**
     * Class constructor
     * @param PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Destructor of the class
     *
     */
    public function __destruct()
    {
        $this->statement->closeCursor();
    }

    /**
     * Count the number of rows affected
     * @return int
     */
    public function count(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Fetch all record
     * @param callable $callable
     * @param int $fetchStyle the PDO fetch style
     * @return array<int, mixed>|false
     */
    public function all(callable $callable = null, int $fetchStyle = 0)
    {
        if ($callable === null) {
            return $this->statement->fetchAll($fetchStyle);
        }
        return $this->statement->fetchAll($fetchStyle | PDO::FETCH_FUNC, $callable);
    }

    /**
     * Fetch all record per group
     * @param bool $uniq
     * @param callable $callable
     * @return array<int, mixed>|false
     */
    public function allGroup(bool $uniq = false, callable $callable = null)
    {
        $fetchStyle = PDO::FETCH_GROUP | ($uniq ? PDO::FETCH_UNIQUE : 0);

        if ($callable === null) {
            return $this->statement->fetchAll($fetchStyle);
        }
        return $this->statement->fetchAll($fetchStyle | PDO::FETCH_FUNC, $callable);
    }

    /**
     * Fetch one record
     * @param callable $callable
     * @return mixed
     */
    public function get(callable $callable = null)
    {
        $result = $this->statement->fetch();
        $this->statement->closeCursor();
        if ($callable !== null) {
            $result = $callable($result);
        }

        return $result;
    }

    /**
     * Fetch the next record
     *
     * @return mixed
     */
    public function next()
    {
        return $this->statement->fetch();
    }

    /**
     * Close the cursor
     * @return mixed
     */
    public function flush()
    {
        return $this->statement->closeCursor();
    }

    /**
     * Fetch the column record
     * @param int $col 0-indexed number of the column you wish to retrieve
     *
     * @return mixed
     */
    public function column(int $col = 0)
    {
        return $this->statement->fetchColumn($col);
    }

    /**
     * Fetch each result as an associative array
     * @return self
     */
    public function fetchAssoc(): self
    {
        $this->statement->setFetchMode(PDO::FETCH_ASSOC);

        return $this;
    }

    /**
     * Fetch each result as an stdClass
     * @return self
     */
    public function fetchObject(): self
    {
        $this->statement->setFetchMode(PDO::FETCH_OBJ);

        return $this;
    }

    /**
     * Fetch each result as an named
     * @return self
     */
    public function fetchNamed(): self
    {
        $this->statement->setFetchMode(PDO::FETCH_NAMED);

        return $this;
    }

    /**
     * Fetch each result as indexed column
     * @return self
     */
    public function fetchNum(): self
    {
        $this->statement->setFetchMode(PDO::FETCH_NUM);

        return $this;
    }

    /**
     * Fetch each result as key/pair
     * @return self
     */
    public function fetchKeyPair(): self
    {
        $this->statement->setFetchMode(PDO::FETCH_KEY_PAIR);

        return $this;
    }

    /**
     * Fetch each result as an instance of the given class
     * @param string $class the name of the class
     * @param array<int, mixed> $cargs the constructor arguments
     * @return self
     */
    public function fetchClass(string $class, array $cargs = []): self
    {
        $this->statement->setFetchMode(PDO::FETCH_CLASS, $class, $cargs);

        return $this;
    }

    /**
     * Fetch each result and pass to given function
     * @param Closure $closure
     * @return self
     */
    public function fetchCustom(Closure $closure): self
    {
        $closure($this->statement);

        return $this;
    }
}
