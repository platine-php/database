## v2.0.x
Migrate code to support minimum PHP version 8.0  

## v1.0.x
### Change make to opis/database version 4.1.x-dev
**Platine Database** get inspiration from **opis/database**. 
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
- Rename all classes attribute or parameters **$sql** of **SQLStatement** to **$queryStatement**
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
- Change return type to **string[]|Expression[]** of methods **getGroupBy**
- Remove method **getIntoDatabase**


##### Class SelectStatement
- Rename attribute **$have** to **$havingStatment**
- Remove **$database** parameter of method **into**
- Remove method **andHaving**
- Remove **$nulls** parameter of method **orderBy**


##### Class SubQuery
- Rename method **getSQLStatement** to **getQueryStatement**


##### Class Where
- Rename method **notNull** to **isNotNull**
- Remove methods **lessThan, greaterThan, atLeast, atMost**


##### Class WhereStatement
- Rename method **addWhereExistCondition** to **addWhereExistsCondition**
- Rename method **getSQLStatement** to **getQueryStatement**
- Rename parameter **$isExpr** of methods **where, orWhere** to **$isExpression**
- Remove method **andWhereExists**


##### Class CreateColumn
- Change return type to **CreateTable** of methods **getTable**

##### Class CreateTable
- In method **autoincrement** set value of attribute **$autoincrement** to true


##### Class ForeignKey
- Rename attribute **$refTable** to **$referenceTable**
- Rename attribute **$refColumns** to **$referenceColumns**
- Rename method **getReferencedTable** to **getReferenceColumns**

##### Class Schema\Compiler and SQL\Compiler
- Merge the two classes to **Driver** class
- Add **description** in the array list of **$modifiers** attribute 
- Rename attribute **$wrapper** to **$identifier**
- Rename all methods **handleXXX** to **getXXX**
- Rename method **Schema\Compiler\handleColumns** to **getSchemaColumns**
- Rename method **Schema\Compiler\currentDatabase** to **getDatabaseName**
- Rename method **SQL\Compiler\handleTables** to **getTableList**
- Rename method **SQL\Compiler\handleGroupings** to **getGroupBy**
- Rename method **SQL\Compiler\handleHavings** to **getHaving**
- Rename method **SQL\Compiler\handleOrderings** to **getOrders**
- Rename method **SQL\Compiler\whereSubquery** to **whereSubQuery**
- Rename method **wrap** to **quoteIdentifier**
- Rename method **wrapArray** to **quoteIdentifiers**
- Add method **getModifierDescription**
- Add method **getWheresHaving**
- Rename parameter **$func** of methods **aggregateFunctionXXX** to **$function**
- Remove methods **SQL\Compiler\{sqlFunctionUCASE, sqlFunctionLCASE, sqlFunctionMID, sqlFunctionLEN, sqlFunctionROUND, sqlFunctionNOW, sqlFunctionFORMAT}

##### Class SQLite
- Rewrite method **truncate**
- Rename attribute **$nopk** to **$noPrimaryKey**

##### Class ResultSet
- Rename method **first** to **get**
- Remove method **fetchBoth**
- Rename parameter **$func** of method **fetchCustom** to **$closure**

##### Class Schema
- Rename attribute **$tableList** to **$tables**
- Rename attribute **$currentDatabase** to **$databaseName**
- Rename method **getCurrentDatabase** to **getDatabaseName**
- Rename parameter **$clear** of methods **hasTable, getTables, getColumns** to **$skipCache**
- Change return type to **string[]|array<string, array<string, string>>** of method **getColumns**
