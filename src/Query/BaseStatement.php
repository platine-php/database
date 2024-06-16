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
 *  @file BaseStatement.php
 *
 *  The base statement class
 *
 *  @package    Platine\Database\Query
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Database\Query;

use Closure;

/**
 * @class BaseStatement
 * @package Platine\Database\Query
 */
class BaseStatement extends WhereStatement
{
    /**
     * @param string|string[] $table
     * @param Closure $closure
     *
     * @return Delete|Select|BaseStatement
     */
    public function join($table, Closure $closure)
    {
        $this->queryStatement->addJoinClause('INNER', $table, $closure);

        return $this;
    }

    /**
     * @param string|string[] $table
     * @param Closure $closure
     *
     * @return Delete|Select|BaseStatement
     */
    public function leftJoin($table, Closure $closure)
    {
        $this->queryStatement->addJoinClause('LEFT', $table, $closure);

        return $this;
    }

    /**
     * @param string|string[] $table
     * @param Closure $closure
     *
     * @return Delete|Select|BaseStatement
     */
    public function rightJoin($table, Closure $closure)
    {
        $this->queryStatement->addJoinClause('RIGHT', $table, $closure);

        return $this;
    }

    /**
     * @param string|string[] $table
     * @param Closure $closure
     *
     * @return Delete|Select|BaseStatement
     */
    public function fullJoin($table, Closure $closure)
    {
        $this->queryStatement->addJoinClause('FULL', $table, $closure);

        return $this;
    }

    /**
     * @param string|string[] $table
     * @param Closure $closure
     *
     * @return Delete|Select|BaseStatement
     */
    public function crossJoin($table, Closure $closure)
    {
        $this->queryStatement->addJoinClause('CROSS', $table, $closure);

        return $this;
    }
}
