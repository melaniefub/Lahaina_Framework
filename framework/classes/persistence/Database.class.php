<?php

    namespace lahaina\framework\persistence;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Logger;
use lahaina\framework\common\Config;
use lahaina\framework\exception\FrameworkException;
use lahaina\framework\data\Collection;

    /**
     * Database
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Database {

	/**
	 * @var \PDO
	 */
	protected $_pdo;

	/**
	 * @var \PDOStatement
	 */
	protected $_statement;

	/**
	 * @var Logger
	 */
	private $_logger;
	private $_connectionName;

	/**
	 * Constructor
	 *
	 * @param string $name Connection name 
	 * @param \lahaina\framework\common\Config $dbConfig Configration of database connection
	 * @param \lahaina\framework\common\Logger $logger Logger
	 */
	public function __construct($name, Config $dbConfig, Logger $logger) {
	    $this->_logger = $logger;
	    $this->_connectionName = $name;
	    $this->_connect($dbConfig);
	}

	/**
	 * Connect to database
	 * 
	 * @param \lahaina\framework\common\Config $dbConfig Configration of database connection
	 */
	private function _connect(Config $dbConfig) {
	    $this->_logger->info('Connecting to ' . $dbConfig->get('driver') . ' database (' . $this->_connectionName . ')', $this);

	    $driver = $dbConfig->get('driver');
	    $host = $dbConfig->get('host');
	    $username = $dbConfig->get('username');
	    $password = $dbConfig->get('password');
	    $database = $dbConfig->get('database');
	    $charset = $dbConfig->get('charset');

	    $this->_pdo = new \PDO($driver . ':host=' . $host . ';dbname=' . $database . ';charset=' . $charset, $username, $password);
	    $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	    $this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	}

	/**
	 * Get connection name
	 * 
	 * @return string
	 */
	public function getConnectionName() {
	    return $this->_connectionName;
	}

	/**
	 * Get PDO instance
	 * 
	 * @return \PDO
	 */
	public function getPdoInstance() {
	    return $this->_pdo;
	}

	/**
	 * Get PDO statement
	 * 
	 * @return \PDOStatement
	 */
	public function getPdoStatement() {
	    return $this->_statement;
	}

	/**
	 * Prepare statement
	 *
	 * @param string $query SQL query
	 * @return \lahaina\framework\persistence\Database
	 */
	public function prepare($query) {
	    $this->_logger->debug('Preparing statement (' . $query . ')', $this);
	    $this->_statement = $this->_pdo->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
	    return $this;
	}

	/**
	 * Fetch all rows of executed statement
	 * 
	 * @return \lahaina\framework\data\Collection
	 */
	public function fetchAll() {
	    $this->_logger->debug('Fetching all rows', $this);
	    $rows = $this->_statement->fetchAll(\PDO::FETCH_CLASS, '\lahaina\framework\data\Container');
	    return new Collection($rows, '\lahaina\framework\data\Container');
	}

	/**
	 * Fetch one row of executed statement
	 * 
	 * @return \lahaina\framework\data\Container
	 * @throws FrameworkException
	 */
	public function fetchOne() {
	    $this->_logger->debug('Fetching one row', $this);
	    $rows = $this->_statement->fetchAll(\PDO::FETCH_CLASS, '\lahaina\framework\data\Container');
	    return isset($rows[0]) ? $rows[0] : null;
	}

	/**
	 * Fetch all rows as array of executed statement
	 * 
	 * @return array
	 */
	public function fetchAllAssoc() {
	    $this->_logger->debug('Fetching all rows as array', $this);
	    return $this->_statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Execute statement
	 * 
	 * @param array $params An array of parameters to be bound to the query
	 * @return \lahaina\framework\persistence\Database
	 */
	public function execute($params = null) {
	    $this->_logger->debug('Executing statement', $this);
	    $this->_statement->execute($params);
	    return $this;
	}

	/**
	 * Get last inserted ID
	 * 
	 * @return mixed
	 */
	public function getLastInsertId() {
	    return $this->_pdo->lastInsertId();
	}

	/**
	 * Get number of changed rows
	 * 
	 * @return float
	 */
	public function getRowCount() {
	    return $this->_statement->rowCount();
	}

	/**
	 * Bind value to a parameter of a statement
	 * 
	 * @param string $param Parameter
	 * @param string $value Value
	 * @param Type $type Value type
	 * @return \lahaina\framework\persistence\Database
	 * @throws FrameworkException
	 */
	public function bindValue($param, $value, $type = Type::STRING) {
	    $this->_logger->debug('Binding value to parameter (' . $param . ' => ' . $value . ')', $this);

	    if ($value === '') {
		$value = null;
		$type = Type::NULL;
	    }

	    switch ($type) {
		case 'null':
		    $type = \PDO::PARAM_NULL;
		    break;
		case 'string':
		    $type = \PDO::PARAM_STR;
		    break;
		case 'integer':
		    $type = \PDO::PARAM_INT;
		    break;
		case 'blob':
		    $type = \PDO::PARAM_LOB;
		    break;
		case 'boolean':
		    $type = \PDO::PARAM_BOOL;
		    break;
		default:
		    throw new FrameworkException($type . ' is not a value type');
	    }

	    $this->_statement->bindValue($param, $value);
	    return $this;
	}

	/**
	 * Bind values to multiple parameters of a statement
	 * 
	 * @param array $values An array of parameters to be bound in to the query
	 * @return \lahaina\framework\persistence\Database
	 * @throws FrameworkException
	 */
	public function bindValues($values) {
	    if ($values != null) {
		foreach ($values as $param => $value) {
		    $this->bindValue($param, $value);
		}
	    }
	    return $this;
	}

    }
    