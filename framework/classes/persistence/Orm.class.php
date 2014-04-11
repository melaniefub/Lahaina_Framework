<?php

    namespace lahaina\framework\persistence;

use lahaina\framework\data\Collection;
use lahaina\framework\common\Lahaina;
use lahaina\framework\exception\FrameworkException;
use lahaina\framework\data\Container;

    /**
     * Object relation mapper
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     * 
     * @see Modified solution of: http://j4mie.github.io/idiormandparis/
     */
    class Orm {

	protected $_conditionFragment = 0;
	protected $_conditionValues = 1;
	// Map of configuration settings
	protected $_config = array(
	    'identifier_quote_character' => '`', // if this is null, will be autodetected
	    'caching' => false,
	    'return_array' => false,
	);

	/**
	 * @var Database
	 */
	protected $_db;

	/**
	 * @var Lahaina
	 */
	protected $_lahaina;
	// The name of the table the current ORM instance is associated with
	protected $_tableName;
	protected $_idColumn = 'id';
	// Query cache, only used if query caching is enabled
	protected $_queryCache = array();
	// Alias for the table to be used in SELECT queries
	protected $_tableAlias = null;
	// Values to be bound to the query
	protected $_values = array();
	// Columns to select in the result
	protected $_resultColumns = array('*');
	// Are we using the default result column or have these been manually changed?
	protected $_usingDefaultResultColumns = true;
	// Join sources
	protected $_joinSources = array();
	// Should the query include a DISTINCT keyword?
	protected $_distinct = false;
	// Is this a raw query?
	protected $_isRawQuery = false;
	// The raw query
	protected $_rawQuery = '';
	// The raw query parameters
	protected $_rawParameters = array();
	// Array of WHERE clauses
	protected $_whereConditions = array();
	// LIMIT
	protected $_limit = null;
	// OFFSET
	protected $_offset = null;
	// ORDER BY
	protected $_orderBy = array();
	// GROUP BY
	protected $_groupBy = array();
	// HAVING
	protected $_havingConditions = array();
	protected $_data = array();
	protected $_dirtyFields = array();
	// Fields that are to be inserted in the DB raw
	protected $_exprFields = array();
	// Is this a new object (has create() been called)?
	protected $_isNew = false;
	// The name of model the current ORM instance is associated with
	protected $_modelClassName;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\persistence\orm\Database $db Database connection
	 * @param array $array Configuration array of ORM
	 */

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina
	 * @param sting $connectionName Database connection name
	 */
	public function __construct(Lahaina $lahaina, $connectionName = null) {
	    $lahaina->logger()->debug('Create ORM', $this);
	    $this->_lahaina = $lahaina;
	    if ($connectionName) {
		$this->_db = $this->_lahaina->database($connectionName);
	    } else {
		$this->_db = $this->_lahaina->database();
	    }
	}

	/**
	 * Set ORM for table
	 *  
	 * @param string $tableName Table name
	 * @param string $idColumn Identifier column name
	 * @param sting $connectionName Database connection name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function forTable($tableName, $idColumn = 'id', $connectionName = null) {
	    $this->_lahaina->logger()->debug('ORM factory (' . lcfirst($tableName) . ' table)', $this);
	    $this->_tableName = $tableName;
	    $this->_idColumn = $idColumn;
	    $this->setConnection($connectionName);
	    return $this;
	}

	/**
	 * Set connection
	 * 
	 * @param string $connectionName Database connection name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function setConnection($connectionName) {
	    if (is_string($connectionName)) {
		$this->_db = $this->_lahaina->database($connectionName);
	    } else {
		$this->_db = $this->_lahaina->database();
	    }
	    return $this;
	}

	/**
	 * Set ORM for model entity
	 *  
	 * @param string $name Model entity name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function factory($name) {
	    $this->_lahaina->logger()->debug('ORM factory (' . lcfirst($name) . ' model)', $this);
	    $modelClassName = $this->_buildModelClassName($name);
	    $this->_tableName = $modelClassName::TABLE_NAME;
	    $this->_idColumn = $modelClassName::ID_COLUMN;
	    $this->setConnection($modelClassName::CONNECTION_NAME);
	    $this->_modelClassName = $modelClassName;
	    return $this;
	}

	/**
	 * Build the model class name
	 */
	protected function _buildModelClassName($name) {
	    $modelClassName = 'application\\models\\' . ucfirst($name) . '_Model';
	    if (class_exists($modelClassName)) {
		return $modelClassName;
	    } else {
		throw new FrameworkException('Cannot use table name ' . $name . ' because model class ' . $modelClassName . ' does not exists.');
	    }
	}

	/**
	 * Get the ID
	 * 
	 * @return mixed
	 */
	public function id() {
	    $idColumnName = $this->_getIdColumnName();
	    try {
		return $this->get($idColumnName);
	    } catch (\Exception $ex) {
		return null;
	    }
	}

	/**
	 * Get the name of the table the current ORM instance is associated with
	 * 
	 * @return string
	 */
	public function getTableName() {
	    return $this->_tableName;
	}

	/**
	 * Executes a raw query
	 * 
	 * @param string $query The raw SQL query
	 * @param array $params Optional bound parameters
	 * @return boolean
	 */
	public function rawExecute($query, $params = array()) {
	    return $this->_db->prepare($query)->execute($params);
	}

	/**
	 * Configure ORM instance for table
	 * 
	 * @param array $array Configuration array
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function configure($config = null) {
	    $this->_lahaina->logger()->debug('Configure ORM', $this);
	    if (is_array($config)) {
		foreach ($config as $key => $value) {
		    if ($key === 'connection') {
			$this->_db = $this->_lahaina->database($value);
		    }
		    $this->_config[$key] = $value;
		}
		return $this;
	    } else {
		throw new FrameworkException('ORM configuration must be an array');
	    }
	}

	/**
	 * Find a single result of your query as an object
	 * 
	 * @param mixed $id ID value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function findOne($id = null) {
	    $this->_lahaina->logger()->debug('Find one entity', $this);
	    if (!is_null($id)) {
		$this->whereIdentfierIs($id);
	    }
	    $this->limit(1);

	    $rows = $this->_run();

	    if (isset($rows[0])) {
		$row = $rows[0];
	    } else {
		$row = array();
	    }

	    return $this->_createInstanceFromRow($row);
	}

	/**
	 * Find multiple results of your query as objects
	 * 
	 * @return mixed
	 */
	public function findAll() {
	    $this->_lahaina->logger()->debug('Find all entities', $this);
	    $rows = $this->_run();

	    $results = array();
	    foreach ($rows as $key => $row) {
		$results[$key] = $this->_createInstanceFromRow($row);
	    }

	    if ($this->_config['return_array']) {
		return $results;
	    } else {
		return new Collection($results);
	    }
	}

	/**
	 * Find multiple results of your query as arrays
	 * 
	 * @return array
	 */
	public function findAllAssoc() {
	    $this->_lahaina->logger()->debug('Find all entities as array', $this);
	    return $this->_run();
	}

	/**
	 * Count results of your query
	 * 
	 * @param string $column
	 * @return integer
	 */
	public function count($column = '*') {
	    $this->_lahaina->logger()->debug('Count number of entities', $this);
	    return $this->_executeSqlFunction('COUNT', $column);
	}

	/**
	 * Get max value from choosen column of your query
	 * 
	 * @param string $column
	 * @return mixed
	 */
	public function max($column) {
	    return $this->_executeSqlFunction('MAX', $column);
	}

	/**
	 * Get the min value from choosen column of your query
	 * 
	 * @param string $column
	 * @return mixed
	 */
	public function min($column) {
	    return $this->_executeSqlFunction('MIN', $column);
	}

	/**
	 * Get the average value from choosen column of your query
	 * 
	 * @param type $column
	 * @return type
	 */
	public function avg($column) {
	    return $this->_executeSqlFunction('AVG', $column);
	}

	/**
	 * Get the sum from choosen colum of your query
	 * 
	 * @param type $column
	 * @return type
	 */
	public function sum($column) {
	    return $this->_executeSqlFunction('SUM', $column);
	}

	/**
	 * Delete this record from the database
	 * 
	 * @return float
	 */
	public function delete() {
	    $this->_lahaina->logger()->debug('Delete entity', $this);
	    $query = join(" ", array(
		"DELETE FROM",
		$this->_quoteIdentifier($this->_tableName),
		"WHERE",
		$this->_quoteIdentifier($this->_getIdColumnName()),
		"= ?",
	    ));
	    return $this->_db->prepare($query)->execute(array($this->id()))->getRowCount();
	}

	/**
	 * Delete all records of your query from the database
	 * 
	 * @return float
	 */
	public function deleteAll() {
	    $this->_lahaina->logger()->debug('Delete all records', $this);
	    // Build and return the full DELETE statement by concatenating
	    // the results of calling each separate builder method.
	    $query = $this->_joinIfNotEmpty(" ", array(
		"DELETE FROM",
		$this->_quoteIdentifier($this->_tableName),
		$this->_buildWhere(),
	    ));

	    return $this->_db->prepare($query)->execute($this->_values)->getRowCount();
	}

	/**
	 * Perform a raw query
	 * 
	 * @param string $query The raw SQL query
	 * @param array $params Optional bound parameters
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function rawQuery($query, $parameters = array()) {
	    $this->_isRawQuery = true;
	    $this->_rawQuery = $query;
	    $this->_rawParameters = $parameters;
	    return $this;
	}

	/**
	 * Set table alias for table
	 * 
	 * @param string $alias Alias for main table
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function setTableAlias($alias) {
	    $this->_tableAlias = $alias;
	    return $this;
	}

	/**
	 * Add a DISTINCT keyword before the list of columns in the SELECT query
	 * 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function distinct() {
	    $this->_distinct = true;
	    return $this;
	}

	/**
	 * Add a column to the list of returned columns
	 * 
	 * @param string $column Column name
	 * @param string $alias Alias for the column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function select($column, $alias = null) {
	    $column = $this->_quoteIdentifier($column);
	    return $this->_addResultColumn($column, $alias);
	}

	/**
	 * Add an unquoted expression to the list of returned columns
	 * 
	 * @param string $expr SQL expression
	 * @param string $alias Alias for the column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function selectExpr($expr, $alias = null) {
	    return $this->_addResultColumn($expr, $alias);
	}

	/**
	 * Add columns to the list of returned columns
	 * 
	 * @example selectMany(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5');
	 * @example selectMany(array('column', 'column2', 'column3'), 'column4', 'column5');
	 * 
	 * @param array $columns Column names
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function selectMany($columns = array()) {
	    if (is_array($columns)) {
		$columns = $this->_normaliseSelectManyColumns($columns);
		foreach ($columns as $alias => $column) {
		    if (is_numeric($alias)) {
			$alias = null;
		    }
		    $this->select($column, $alias);
		}
	    } else {
		throw new FrameworkException('Columns must be definied in an array');
	    }
	    return $this;
	}

	/**
	 * Add unquoted expressions to the list of returned columns
	 * 
	 * @example selectManyExpr(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5');
	 * @example selectManyExpr(array('column', 'column2', 'column3'), 'column4', 'column5');
	 * 
	 * @param array $columns Column names
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function selectManyExpr($columns = array()) {
	    if (is_array($columns)) {
		$columns = $this->_normaliseSelectManyColumns($columns);
		foreach ($columns as $alias => $column) {
		    if (is_numeric($alias)) {
			$alias = null;
		    }
		    $this->selectExpr($column, $alias);
		}
	    }
	    return $this;
	}

	/**
	 * Add a simple JOIN source to the query
	 * 
	 * @param string $table Table name to join
	 * @param mixed $constraint Join constraint as array (first_column, operator, second_column) or string
	 * @param string $tableAlias Alias name for table 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function join($table, $constraint, $tableAlias = null) {
	    return $this->_addJoinSource("", $table, $constraint, $tableAlias);
	}

	/**
	 * Add an INNER JOIN source to the query
	 * 
	 * @param string $table Table name to join
	 * @param mixed $constraint Join constraint as array (first_column, operator, second_column) or string
	 * @param string $tableAlias Alias name for table 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function joinInner($table, $constraint, $tableAlias = null) {
	    return $this->_addJoinSource("INNER", $table, $constraint, $tableAlias);
	}

	/**
	 * Add a LEFT OUTER JOIN source to the query
	 * 
	 * @param string $table Table name to join
	 * @param mixed $constraint Join constraint as array (first_column, operator, second_column) or string
	 * @param string $tableAlias Alias name for table 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function joinLeftOuter($table, $constraint, $tableAlias = null) {
	    return $this->_addJoinSource("LEFT OUTER", $table, $constraint, $tableAlias);
	}

	/**
	 * Add a RIGHT OUTER JOIN source to the query
	 * 
	 * @param string $table Table name to join
	 * @param mixed $constraint Join constraint as array (first_column, operator, second_column) or string
	 * @param string $tableAlias Alias name for table 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function joinRightOuter($table, $constraint, $tableAlias = null) {
	    return $this->_addJoinSource("RIGHT OUTER", $table, $constraint, $tableAlias);
	}

	/**
	 * Add a FULL OUTER JOIN source to the query
	 * 
	 * @param string $table Table name to join
	 * @param mixed $constraint Join constraint as array (first_column, operator, second_column) or string
	 * @param string $tableAlias Alias name for table 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function joinFullOuter($table, $constraint, $tableAlias = null) {
	    return $this->_addJoinSource("FULL OUTER", $table, $constraint, $tableAlias);
	}

	/**
	 * Add a WHERE column = value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function where($columnName, $value) {
	    return $this->whereEqual($columnName, $value);
	}

	/**
	 * Add a WHERE column = value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereEqual($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '=', $value);
	}

	/**
	 * Add a WHERE column != value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereNotEqual($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '!=', $value);
	}

	/**
	 * Add a WHERE primary key = value clause to your query
	 * 
	 * @param string $id Identifier
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereIdentfierIs($id) {
	    return $this->where($this->_getIdColumnName(), $id);
	}

	/**
	 * Add a WHERE column LIKE value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereLike($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, 'LIKE', $value);
	}

	/**
	 * Add a WHERE column NOT LIKE value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereNotLike($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, 'NOT LIKE', $value);
	}

	/**
	 * Add a WHERE column > value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereGreater($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '>', $value);
	}

	/**
	 * Add a WHERE column < value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereLess($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '<', $value);
	}

	/**
	 * Add a WHERE column >= value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereGreaterOrEqual($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '>=', $value);
	}

	/**
	 * Add a WHERE ... <= clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereLessOrEqual($columnName, $value) {
	    return $this->_addSimpleWhere($columnName, '<=', $value);
	}

	/**
	 * Add a WHERE column IN values clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param array $values Clause values
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereIn($columnName, $values) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $placeholders = $this->_createPlaceholders($values);
	    return $this->_addWhere("{$columnName} IN ({$placeholders})", $values);
	}

	/**
	 * Add a WHERE column NOT IN values clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param array $values Clause values
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereNotIn($columnName, $values) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $placeholders = $this->_createPlaceholders($values);
	    return $this->_addWhere("{$columnName} NOT IN ({$placeholders})", $values);
	}

	/**
	 * Add a WHERE column IS NULL clause to your query
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereNull($columnName) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    return $this->_addWhere("{$columnName} IS NULL");
	}

	/**
	 * Add a WHERE column IS NOT NULL clause to your query
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereNotNull($columnName) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    return $this->_addWhere("{$columnName} IS NOT NULL");
	}

	/**
	 * Add a raw WHERE clause to the query
	 * 
	 * @param array $values Where clause with placeholders
	 * @param array $parameters Parameters for placeholders
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function whereRaw($clause, $parameters = array()) {
	    return $this->_addWhere($clause, $parameters);
	}

	/**
	 * Add a LIMIT to the query
	 * 
	 * @param mixed $limit Number of limit
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function limit($limit) {
	    $this->_limit = $limit;
	    return $this;
	}

	/**
	 * Add an OFFSET to the query
	 * 
	 * @param mixed $offset Number of offset
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function offset($offset) {
	    $this->_offset = $offset;
	    return $this;
	}

	/**
	 * Add an ORDER BY column clause
	 * 
	 * @param string $columnName Column name
	 * @param string $ordering Ordering (ASC, DESC, ...)
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function orderBy($columnName, $ordering) {
	    return $this->_addOrderBy($columnName, $ordering);
	}

	/**
	 * Add an ORDER BY column DESC clause
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function orderByDesc($columnName) {
	    return $this->_addOrderBy($columnName, 'DESC');
	}

	/**
	 * Add an ORDER BY column ASC clause
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function orderByAsc($columnName) {
	    return $this->_addOrderBy($columnName, 'ASC');
	}

	/**
	 * Add an unquoted expression as an ORDER BY clause
	 * 
	 * @param string $clause ORDER BY clause
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function orderByRaw($clause) {
	    $this->_orderBy[] = $clause;
	    return $this;
	}

	/**
	 * Add a column to the list of columns to GROUP BY
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function groupBy($columnName) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $this->_groupBy[] = $columnName;
	    return $this;
	}

	/**
	 * Add an unquoted expression to the list of columns to GROUP BY 
	 * 
	 * @param string $clause GROUP BY clause
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function groupByRaw($clause) {
	    $this->_groupBy[] = $clause;
	    return $this;
	}

	/**
	 * Add a HAVING column = value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function having($columnName, $value) {
	    return $this->havingEqual($columnName, $value);
	}

	/**
	 * Add a HAVING column = value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingEqual($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '=', $value);
	}

	/**
	 * Add a HAVING column != value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingNotEqual($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '!=', $value);
	}

	/**
	 * Add a HAVING primary key = value clause to your query
	 * 
	 * @param string $id Identifier
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingIdentfierIs($id) {
	    return $this->having($this->_getIdColumnName(), $id);
	}

	/**
	 * Add a HAVING column LIKE value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingLike($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, 'LIKE', $value);
	}

	/**
	 * Add a HAVING column NOT LIKE value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingNotLike($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, 'NOT LIKE', $value);
	}

	/**
	 * Add a HAVING column > value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingGreater($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '>', $value);
	}

	/**
	 * Add a HAVING column < value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingLess($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '<', $value);
	}

	/**
	 * Add a HAVING column >= value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingGreaterOrEqual($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '>=', $value);
	}

	/**
	 * Add a HAVING column <= value clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param string $value Clause value
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingLowerOrEqual($columnName, $value) {
	    return $this->_addSimpleHaving($columnName, '<=', $value);
	}

	/**
	 * Add a HAVING column IN values clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param array $values Clause values
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingIn($columnName, $values) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $placeholders = $this->_createPlaceholders($values);
	    return $this->_addHaving("{$columnName} IN ({$placeholders})", $values);
	}

	/**
	 * Add a HAVING column NOT IN values clause to your query
	 * 
	 * @param string $columnName Column name
	 * @param array $values Clause values
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingNotIn($columnName, $values) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $placeholders = $this->_createPlaceholders($values);
	    return $this->_addHaving("{$columnName} NOT IN ({$placeholders})", $values);
	}

	/**
	 * Add a HAVING column IS NULL clause to your query
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function having_null($columnName) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    return $this->_addHaving("{$columnName} IS NULL");
	}

	/**
	 * Add a HAVING column IS NOT NULL clause to your query
	 * 
	 * @param string $columnName Column name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingNotNull($columnName) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    return $this->_addHaving("{$columnName} IS NOT NULL");
	}

	/**
	 * Add a raw HAVING clause to the query
	 * 
	 * @param array $values HAVING clause with parameter placeholders
	 * @param array $parameters Parameters for placeholders
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function havingRaw($clause, $parameters = array()) {
	    return $this->_addHaving($clause, $parameters);
	}

	/**
	 * Clear the query cache
	 */
	public function clearCache() {
	    $this->_queryCache = array();
	}

	/**
	 * Set data field value
	 * 
	 * @param string $key Field name
	 * @param mixed $value Value
	 */
	public function set($key, $value = null) {
	    $this->_data[$key] = $value;
	    $this->_dirtyFields[$key] = $value;
	    if (isset($this->_exprFields[$key])) {
		unset($this->_exprFields[$key]);
	    }
	}

	/**
	 * Get data field value
	 * 
	 * @param string $key Field name
	 * @return mixed
	 */
	public function get($key) {
	    if ($this->exists($key)) {
		return $this->_data[$key];
	    } else {
		return null;
	    }
	}

	/**
	 * Check wether data field exists
	 * 
	 * @param string $key Field name
	 * @return boolean
	 */
	public function exists($key) {
	    return isset($this->_data[$key]);
	}

	/**
	 * Remove data field
	 * 
	 * @param string $key Field name
	 */
	public function remove($key) {
	    unset($this->_data[$key]);
	    unset($this->_dirtyFields[$key]);
	}

	/**
	 * Get data field value
	 * 
	 * @param string $key Field name
	 * @return mixed
	 */
	public function __get($key) {
	    return $this->get($key);
	}

	/**
	 * Set data field value
	 * 
	 * @param string $key Field name
	 * @param mixed $value Value
	 */
	public function __set($key, $value) {
	    return $this->set($key, $value);
	}

	/**
	 * Remove data field
	 * 
	 * @param string $key Field name
	 */
	public function __unset($key) {
	    $this->remove($key);
	}

	/**
	 * Check wether data field exists
	 * 
	 * @param string $key Field name
	 * @return boolean
	 */
	public function __isset($key) {
	    return $this->exists($key);
	}

	/**
	 * Create new data of ORM instance 
	 * 
	 * @param mixed $data Data
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function create($data) {
	    $this->update($data);
	    $this->_isNew = true;
	    return $this;
	}

	/**
	 * Force to flag all data fields of the ORM instance as dirty
	 * 
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function forceAllDirty() {
	    $this->_dirtyFields = $this->_data;
	    return $this;
	}

	/**
	 * Update data of ORM instance
	 * 
	 * @param mixed $data Data
	 * @return \lahaina\framework\persistence\Orm
	 * @throws FrameworkException
	 */
	public function update($data) {
	    if (is_array($data) || $data instanceof Container) {
		foreach ($data as $key => $value) {
		    $this->set($key, $value);
		}
		return $this->forceAllDirty();
	    } else {
		throw new FrameworkException('Data must be an array or an instance of \lahaina\framework\data\Container');
	    }
	}

	/**
	 * Get data of ORM instance
	 * 
	 * @return \lahaina\framework\data\Container
	 */
	public function getData() {
	    return new Container($this->_data);
	}

	/**
	 * Set expression to data field value
	 * 
	 * @param string $key Field name
	 * @param mixed $expr SQL expression
	 */
	public function setExpr($key, $expr) {
	    $this->_data[$key] = $expr;
	    $this->_dirtyFields[$key] = $expr;
	    $this->_exprFields[$key] = true;
	}

	/**
	 * Check whether the given field of the data has been changed since this
	 * object was saved
	 * 
	 * @return boolean
	 */
	public function isDirty($key = null) {
	    if ($key) {
		return isset($this->_dirtyFields[$key]);
	    } else {
		return count($this->_dirtyFields) > 0;
	    }
	}

	/**
	 * Check whether the data was the result of a call to create() or not
	 * 
	 * @return boolean
	 */
	public function isNew() {
	    return $this->_isNew;
	}

	/**
	 * Save any fields which have been modified on this object
	 * to the database
	 * 
	 * @return float
	 */
	public function save() {
	    $query = array();

	    // remove any expression fields as they are already baked into the query
	    $values = array_values(array_diff_key($this->_dirtyFields, $this->_exprFields));

	    if (!$this->_isNew) { // UPDATE
		// If there are no dirty values, do nothing
		if (empty($values) && empty($this->_exprFields)) {
		    return true;
		}
		$query = $this->_buildUpdate();
		$values[] = $this->id();
	    } else { // INSERT
		$query = $this->_buildInsert();
	    }

	    $rowCount = $this->_db->prepare($query)->execute($values)->getRowCount();

	    // If we've just inserted a new record, set the ID of this object
	    if ($this->_isNew) {
		$this->_isNew = false;
		if (is_null($this->id())) {
		    $this->_data[$this->_getIdColumnName()] = $this->_db->getLastInsertId();
		}
	    }

	    $this->_dirtyFields = array();
	    return $rowCount;
	}

	/**
	 * Build a SELECT statement based on the clauses that have
	 * been passed to this instance by chaining method calls.
	 */
	protected function _buildSelect() {
	    // If the query is raw, just set the $this->_values to be
	    // the raw query parameters and return the raw query
	    if ($this->_isRawQuery) {
		$this->_values = $this->_rawParameters;
		return $this->_rawQuery;
	    }

	    // Build and return the full SELECT statement by concatenating
	    // the results of calling each separate builder method.
	    return $this->_joinIfNotEmpty(" ", array(
			$this->_buildSelectStart(),
			$this->_buildJoin(),
			$this->_buildWhere(),
			$this->_buildGroupBy(),
			$this->_buildHaving(),
			$this->_buildOrderBy(),
			$this->_buildLimit(),
			$this->_buildOffset(),
	    ));
	}

	/**
	 * Build the start of the SELECT statement
	 */
	protected function _buildSelectStart() {
	    $result_columns = join(', ', $this->_resultColumns);

	    if ($this->_distinct) {
		$result_columns = 'DISTINCT ' . $result_columns;
	    }

	    $fragment = "SELECT {$result_columns} FROM " . $this->_quoteIdentifier($this->_tableName);

	    if (!is_null($this->_tableAlias)) {
		$fragment .= " " . $this->_quoteIdentifier($this->_tableAlias);
	    }
	    return $fragment;
	}

	/**
	 * Build the JOIN sources
	 */
	protected function _buildJoin() {
	    if (count($this->_joinSources) === 0) {
		return '';
	    }

	    return join(" ", $this->_joinSources);
	}

	/**
	 * Build the WHERE clause(s)
	 */
	protected function _buildWhere() {
	    return $this->_buildConditions('where');
	}

	/**
	 * Build the HAVING clause(s)
	 */
	protected function _buildHaving() {
	    return $this->_buildConditions('having');
	}

	/**
	 * Build GROUP BY
	 */
	protected function _buildGroupBy() {
	    if (count($this->_groupBy) === 0) {
		return '';
	    }
	    return "GROUP BY " . join(", ", $this->_groupBy);
	}

	/**
	 * Build a WHERE or HAVING clause
	 * @param string $type
	 * @return string
	 */
	protected function _buildConditions($type) {
	    $conditions_class_property_name = "_{$type}Conditions";
	    // If there are no clauses, return empty string
	    if (count($this->$conditions_class_property_name) === 0) {
		return '';
	    }

	    $conditions = array();
	    foreach ($this->$conditions_class_property_name as $condition) {
		$conditions[] = $condition[$this->_conditionFragment];
		$this->_values = array_merge($this->_values, $condition[$this->_conditionValues]);
	    }

	    return strtoupper($type) . " " . join(" AND ", $conditions);
	}

	/**
	 * Build ORDER BY
	 */
	protected function _buildOrderBy() {
	    if (count($this->_orderBy) === 0) {
		return '';
	    }
	    return "ORDER BY " . join(", ", $this->_orderBy);
	}

	/**
	 * Build LIMIT
	 */
	protected function _buildLimit() {
	    if (!is_null($this->_limit)) {
		$clause = 'LIMIT';
		if ($this->_db->getPDOInstance()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'firebird') {
		    $clause = 'ROWS';
		}
		return "$clause " . $this->_limit;
	    }
	    return '';
	}

	/**
	 * Build OFFSET
	 */
	protected function _buildOffset() {
	    if (!is_null($this->_offset)) {
		$clause = 'OFFSET';
		if ($this->_db->getPDOInstance()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'firebird') {
		    $clause = 'TO';
		}
		return "$clause " . $this->_offset;
	    }
	    return '';
	}

	/**
	 * Internal method as wrapper around PHP's join function which
	 * only adds the pieces if they are not empty.
	 */
	protected function _joinIfNotEmpty($glue, $pieces) {
	    $filtered_pieces = array();
	    foreach ($pieces as $piece) {
		if (is_string($piece)) {
		    $piece = trim($piece);
		}
		if (!empty($piece)) {
		    $filtered_pieces[] = $piece;
		}
	    }
	    return join($glue, $filtered_pieces);
	}

	/**
	 * Build an UPDATE query
	 */
	protected function _buildUpdate() {
	    $query = array();
	    $query[] = "UPDATE {$this->_quoteIdentifier($this->_tableName)} SET";

	    $field_list = array();
	    foreach ($this->_dirtyFields as $key => $value) {
		if (!array_key_exists($key, $this->_exprFields)) {
		    $value = '?';
		}
		$field_list[] = "{$this->_quoteIdentifier($key)} = $value";
	    }
	    $query[] = join(", ", $field_list);
	    $query[] = "WHERE";
	    $query[] = $this->_quoteIdentifier($this->_getIdColumnName());
	    $query[] = "= ?";
	    return join(" ", $query);
	}

	/**
	 * Build an INSERT query
	 */
	protected function _buildInsert() {
	    $query[] = "INSERT INTO";
	    $query[] = $this->_quoteIdentifier($this->_tableName);
	    $field_list = array_map(array($this, '_quoteIdentifier'), array_keys($this->_dirtyFields));
	    $query[] = "(" . join(", ", $field_list) . ")";
	    $query[] = "VALUES";

	    $placeholders = $this->_createPlaceholders($this->_dirtyFields);
	    $query[] = "({$placeholders})";

	    if ($this->_db->getPDOInstance()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
		$query[] = 'RETURNING ' . $this->_quoteIdentifier($this->_getIdColumnName());
	    }

	    return join(" ", $query);
	}

	/**
	 * Add HAVING condition to the query
	 */
	protected function _addHaving($fragment, $values = array()) {
	    return $this->_addCondition('having', $fragment, $values);
	}

	/**
	 * Add a simple HAVING condition to the query
	 */
	protected function _addSimpleHaving($columnName, $separator, $value) {
	    return $this->_addSimpleCondition('having', $columnName, $separator, $value);
	}

	/**
	 * Add a WHERE condition to the query
	 */
	protected function _addWhere($fragment, $values = array()) {
	    return $this->_addCondition('where', $fragment, $values);
	}

	/**
	 * Add a simple WHERE condition to the query
	 */
	protected function _addSimpleWhere($columnName, $separator, $value) {
	    return $this->_addSimpleCondition('where', $columnName, $separator, $value);
	}

	/**
	 * Add a HAVING or WHERE condition to the query
	 */
	protected function _addCondition($type, $fragment, $values = array()) {
	    $conditionsClassPropertyName = "_{$type}Conditions";
	    if (!is_array($values)) {
		$values = array($values);
	    }
	    array_push($this->$conditionsClassPropertyName, array(
		$this->_conditionFragment => $fragment,
		$this->_conditionValues => $values,
	    ));
	    return $this;
	}

	/**
	 * Compile a simple COLUMN SEPARATOR VALUE
	 * style HAVING or WHERE condition into a string and value ready to
	 * be passed to the _addCondition method. Avoids duplication
	 * of the call to _quoteIdentifier
	 */
	protected function _addSimpleCondition($type, $columnName, $separator, $value) {
	    // Add the table name in case of ambiguous columns
	    if (count($this->_joinSources) > 0 && strpos($columnName, '.') === false) {
		$columnName = "{$this->_tableName}.{$columnName}";
	    }
	    $columnName = $this->_quoteIdentifier($columnName);
	    return $this->_addCondition($type, "{$columnName} {$separator} ?", $value);
	}

	/**
	 * Create a string containing the given number of question marks, 
	 * separated by commas. Eg "?, ?, ?"
	 */
	protected function _createPlaceholders($fields) {
	    if (!empty($fields)) {
		$db_fields = array();
		foreach ($fields as $key => $value) {
		    // Process expression fields directly into the query
		    if (array_key_exists($key, $this->_exprFields)) {
			$db_fields[] = $value;
		    } else {
			$db_fields[] = '?';
		    }
		}
		return implode(', ', $db_fields);
	    }
	}

	/**
	 * Execute a SQL function of your query
	 */
	protected function _executeSqlFunction($sqlFunction, $column) {
	    $alias = strtolower($sqlFunction);
	    $sqlFunction = strtoupper($sqlFunction);
	    if ('*' != $column) {
		$column = $this->_quoteIdentifier($column);
	    }
	    $this->selectExpr($sqlFunction . '(' . $column . ')', $alias);
	    $result = $this->findOne();

	    if ($result !== false && isset($result->$alias)) {
		return (float) $result->$alias;
	    }
	    return 0;
	}

	/**
	 * Create a ORM instance from given row
	 */
	protected function _createInstanceFromRow($row) {
	    if (count($row) > 0) {
		if ($this->_modelClassName) {
		    $model = new $this->_modelClassName($this->_lahaina);
		    $model->update($row);
		    return $model;
		} else {
		    $instance = $this->forTable($this->_tableName, $this->_idColumn, $this->_db->getConnectionName());
		    $instance->configure($this->_config);
		    $instance->_data = $row;
		    return $instance;
		}
	    }
	    return false;
	}

	/**
	 * Add an unquoted expression to the set of returned columns
	 */
	protected function _addResultColumn($expr, $alias = null) {
	    if (!is_null($alias)) {
		$expr .= " AS " . $this->_quoteIdentifier($alias);
	    }

	    if ($this->_usingDefaultResultColumns) {
		$this->_resultColumns = array($expr);
		$this->_usingDefaultResultColumns = false;
	    } else {
		$this->_resultColumns[] = $expr;
	    }
	    return $this;
	}

	/**
	 * Take a column specification for the select many 
	 * methods and convert it into a normalised array of columns and aliases
	 */
	protected function _normaliseSelectManyColumns($columns) {
	    $return = array();
	    foreach ($columns as $column) {
		if (is_array($column)) {
		    foreach ($column as $alias => $name) {
			if (!is_numeric($alias)) {
			    $return[$alias] = $name;
			} else {
			    $return[] = $name;
			}
		    }
		} else {
		    $return[] = $column;
		}
	    }
	    return $return;
	}

	/**
	 * Add a JOIN source to the query
	 */
	protected function _addJoinSource($joinOperator, $table, $constraint, $tableAlias = null) {

	    $joinOperator = trim("{$joinOperator} JOIN");

	    $table = $this->_quoteIdentifier($table);

	    // Add table alias if present
	    if (!is_null($tableAlias)) {
		$tableAlias = $this->_quoteIdentifier($tableAlias);
		$table .= " {$tableAlias}";
	    }

	    // Build the constraint
	    if (is_array($constraint)) {
		list($firstColumn, $operator, $secondColumn) = $constraint;
		$firstColumn = $this->_quoteIdentifier($firstColumn);
		$secondColumn = $this->_quoteIdentifier($secondColumn);
		$constraint = "{$firstColumn} {$operator} {$secondColumn}";
	    }

	    $this->_joinSources[] = "{$joinOperator} {$table} ON {$constraint}";
	    return $this;
	}

	/**
	 * Execute the SELECT query that has been built up by chaining methods
	 * on this ORM instance
	 */
	protected function _run() {
	    $query = $this->_buildSelect();
	    $caching_enabled = $this->_config['caching'];

	    if ($caching_enabled) {
		$cache_key = $this->_createCacheKey($query, $this->_values);
		$cached_result = $this->_hasQueryCache($cache_key);

		if ($cached_result !== false) {
		    return $cached_result;
		}
	    }

	    $result = $this->_db
		    ->prepare($query)
		    ->execute($this->_values)
		    ->fetchAllAssoc();

	    if ($caching_enabled) {
		$this->_cacheQueryResult($cache_key, $result);
	    }

	    // reset Idiorm after executing the query
	    $this->_limit = null;
	    $this->_offset = null;
	    $this->_orderBy = null;
	    $this->_joinSources = array();
	    $this->_values = array();
	    $this->_resultColumns = array('*');
	    $this->_usingDefaultResultColumns = true;
	    $this->_whereConditions = array();

	    return $result;
	}

	/**
	 * Get the name of the identifier (primary key) column
	 */
	protected function _getIdColumnName() {
	    if ($this->_idColumn) {
		return $this->_idColumn;
	    }
	    return $this->_tableName . $this->_lahaina->config()->get('framework')->get('foreignKeySuffix');
	}

	/**
	 * Quote a string as an identifier
	 */
	protected function _quoteIdentifier($identifier) {
	    $parts = explode('.', $identifier);
	    $parts = array_map(array($this, '_quoteIdentifierPart'), $parts);
	    return join('.', $parts);
	}

	/**
	 * Quote a single part of an identifier (using the identifier quote
	 * character specified in the config)
	 */
	protected function _quoteIdentifierPart($part) {
	    if ($part === '*') {
		return $part;
	    }
	    $quote_character = $this->_config['identifier_quote_character'];
	    // double up any identifier quotes to escape them
	    return $quote_character .
		    str_replace($quote_character, $quote_character . $quote_character, $part) .
		    $quote_character;
	}

	/**
	 * Create a cache key for the given query and parameters
	 */
	protected function _createCacheKey($query, $parameters) {
	    $parameter = join(',', $parameters);
	    $key = $query . ':' . $parameter;
	    return sha1($key);
	}

	/**
	 * Check wheter query cache exists
	 */
	protected function _hasQueryCache($cacheKey) {
	    if (isset($this->_queryCache[$cacheKey])) {
		return $this->_queryCache[$cacheKey];
	    }
	    return false;
	}

	/**
	 * Add the given value to the query cache.
	 */
	protected function _cacheQueryResult($cacheKey, $value) {
	    if (!isset($this->_queryCache)) {
		$this->_queryCache = array();
	    }
	    $this->_queryCache[$cacheKey] = $value;
	}

	/**
	 * Add an ORDER BY clause to the query
	 */
	protected function _addOrderBy($columnName, $ordering) {
	    $columnName = $this->_quoteIdentifier($columnName);
	    $this->_orderBy[] = "{$columnName} {$ordering}";
	    return $this;
	}

    }
    