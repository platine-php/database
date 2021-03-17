## PLATINE DATABASE
**Platine Database is the abstraction layer using PDO with support of query and schema builder**

[![Project Status](http://opensource.box.com/badges/active.svg)](http://opensource.box.com/badges)
[![Latest Stable Version](https://poser.pugx.org/platine-php/database/v)](https://packagist.org/packages/platine-php/database)
[![Latest Unstable Version](https://poser.pugx.org/platine-php/database/v/unstable)](https://packagist.org/packages/platine-php/database)
[![Total Downloads](https://poser.pugx.org/platine-php/database/downloads)](https://packagist.org/packages/platine-php/database)
[![License](https://poser.pugx.org/platine-php/database/license)](https://packagist.org/packages/platine-php/database)
[![Build Status](https://img.shields.io/travis/platine-php/database/develop.svg?style=flat-square)](https://travis-ci.com/platine-php/database)  
[![Quality Score](https://img.shields.io/scrutinizer/g/platine-php/database.svg?style=flat-square)](https://scrutinizer-ci.com/g/platine-php/database)
[![Maintainability](https://api.codeclimate.com/v1/badges/15c5b5181e9019729d60/maintainability)](https://codeclimate.com/github/platine-php/database/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/15c5b5181e9019729d60/test_coverage)](https://codeclimate.com/github/platine-php/database/test_coverage)

### Requirements 
- **PHP >= 7.4**, **PHP 8** 

### Installation
#### Using composer (recommended)
```bash
composer require platine-php/database
```
### Current supported drivers

- MySQL/MariaDB
- SQLite
- PostgreSQL
- Oracle
- Microsoft SQL Server

### License
MIT License See [LICENSE.MD](LICENSE.MD)

### Documentation 
Coming soon, be patient

### Change make to opis/database version 4.1.x-dev
**Platine Database** get inspiration from **opis/database**
All notable changes to [opis/database](https://github.com/opis/database) are documented below.

#### Global changes

- Change all namespace **Opis\Database** to **Platine\Database**
- Rename package **Opis\Database\SQL** to **Platine\Database\Query**
- Merge SQL and Schema **Compiler** classes to one class **Platine\Database\Driver\Driver**
- Move all drivers classes from **Opis\Database\Compiler** and **Opis\Database\Schema\Compiler**  to **Platine\Database\Driver**
- Remove drivers classes **DB2, Firebird, NuoDB**
- Update code to PHP 7.4 (typed properties, **object** type, etc.)
- Add **declare(strict_types=1);** in all files
- Rename class **Database** to **QueryBuilder**
- Rename class **SQLStatement** to **QueryStatement**
- Rename class **Subquery** to **SubQuery**
- Rename all classes attribute **$sql** instance of **SQLStatement** to **$queryStatement**
- Add interface **ConfigurationInterface** and classes **Configuration, Pool** 
- Add Exceptions packages under namespace **Platine\Database\Exception**
- Change all typehint array to array notation based on static analysis tools (phpstan), example array<int, string>, etc.
- In each class reorder all methods based on modifier *public -> protected -> private*

#### Details change

##### Class ColumnExpression
- Change parameter typehint **string $alias** to *string|null $alias* of method **column**
- Change parameter typehint **string $alias** to *string|null $alias* of methods **count, avg, sum, min, max** and add typehint *Closure* to parameter **$column**
- Removes methods **ucase, lcase, mid, len, round, format, now**

##### Class Delete
- Add return type **int** for method **delete**

##### Class Expression
- Add typehint **Closure, Expression** to parameter **$column** of methods **count, avg, sum, min, max** 
- Removes methods **ucase, lcase, mid, len, round, format, now, __get**

##### Class Having
- Rename parameter **$is_column** to **$isColumn** of method **addCondition, is, neq, ne, isNot, lt, gt, lte, gte**
- Rename method **ne** to **neq**
- Inside method **eq** call method **is** instead of **addCondition**
- Inside method **neq** call method **isNot** instead of **addCondition**

##### Class HavingExpression
- Add typehint **Closure, Expression** to parameter **$column** of methods **init** 

##### Class HavingStatement
- Rename method **getSQLStatement** to **getQueryStatement**
- Change parameter typehint **Closure $value** to **Closure|null $value** of method **having, orHaving**
- Removes method **andHaving**

##### Class InsertStatement
- Rename method **getSQLStatement** to **getQueryStatement**

##### Class Join
- Add typehint **Closure, Expression** to parameter **$column1** of methods **addJoinCondition** 
- Add typehint **Closure, Expression, bool, null** to parameter **$column2** of methods **addJoinCondition** 


##### Class Query
- Remove **$nulls** parameter of method **orderBy**
- Remove **$database** parameter for method **into**
- Change parameter typehint **array $columns** to **string|string[]|Expression|Expression[]|Closure|Closure[] $columns** of method **select**
- Change return type to **mixed** of methods **min, max**


##### Class QueryStatement
- Remove attribute **$intoDatabase**
- Rename method **addWhereConditionGroup** to **addWhereGroup**
- Rename method **addWhereCondition** to **addWhere**
- Rename method **addWhereLikeCondition** to **addWhereLike**
- Rename method **addWhereBetweenCondition** to **addWhereBetween**
- Rename method **addWhereInCondition** to **addWhereIn**
- Rename method **addWhereNullCondition** to **addWhereNull**
- Rename method **addWhereExistsCondition** to **addWhereExists**
- Add typehint **null** to parameter **$closure** of methods **addJoinClause** 
- Change parameter **$callback** to **$closure** of methods **addHavingGroupCondition** 
- Rename method **addWhereExistsCondition** to **addHavingGroup**
- Rename method **addHavingCondition** to **addHaving**
- Rename method **addHavingInCondition** to **addHavingIn**
- Change typehint **int** of parameters *$value1, $value2* of methods **addHavingBetweenCondition** to *mixed* 
- Rename method **addHavingBetweenCondition** to **addHavingBetween**
- Remove **$nulls** parameter of method **addOrder**
- Add typehint **null** to parameter **$alias** of methods **addColumn** 
- Remove **$database** parameter of method **setInto**
- Rename method **getDistinct** to **hasDistinct**
- Change return type to *string[]|Expression[]* of methods **getGroupBy**
- Remove method **getIntoDatabase**







