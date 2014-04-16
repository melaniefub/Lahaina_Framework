<?php

    namespace lahaina\framework\mvc;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;
use lahaina\framework\exception\FrameworkException;
use lahaina\framework\persistence\Orm;

    /**
     * Model
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Model {

	const ID_COLUMN = 'id';
	const TABLE_NAME = '';
	const CONNECTION_NAME = null;

	/**
	 * @var Orm
	 */
	protected $_orm;

	/**
	 * @var Lahaina
	 */
	protected $_lahaina;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	    $this->_lahaina->logger()->debug('Create model', $this);
	    $this->_orm = new Orm($lahaina);
	    $this->_orm->factory($this->_getEntityName());
	}

	/**
	 * Associate new data to the model instance
	 * 
	 * @param array $data New data of model instance
	 * @param boolean $isNew Model entity is new or not
	 */
	public function create($data = array()) {
	    $this->_orm->create($data);
	    $this->_lahaina->logger()->debug('Create model entity (' . $this->_orm->id() . ')', $this);
	}

	/**
	 * Update this model instance with an associative array of data
	 * 
	 * @param array $data Data for hydration
	 */
	public function update($data = array()) {
	    $this->_orm->update($data);
	    $this->_lahaina->logger()->debug('Update model entity (' . $this->_orm->id() . ')', $this);
	}

	/**
	 * Get model entity data as array
	 * 
	 * @return array
	 */
	public function toArray() {
	    return $this->_orm->getData()->toArray();
	}

	/**
	 * Get model entity property
	 * 
	 * @param mixed $key Property key
	 * @return mixed
	 */
	public function get($key) {
	    if (property_exists($this, $key)) { // as model property
		return $this->$key;
	    } elseif (method_exists($this, $key)) { // as model method
		return $this->{$key}();
	    } elseif (strpos($key, '.') !== false) { // as related model entity property
		$modelName = explode('.', $key)[0];
		$propertyKey = explode('.', $key)[1];
		return $this->{$modelName}()->get($propertyKey);
	    }
	    return $this->_orm->get($key);
	}

	/**
	 * Set value to model entity property
	 * 
	 * @param string $key Property key
	 * @param mixed $value Property value
	 */
	public function set($key, $value = null) {
	    $this->_orm->set($key, $value);
	}

	/**
	 * Check whether the model entity property exists
	 * 
	 * @param string $key Property key
	 * @return boolean
	 */
	public function exists($key) {
	    return $this->_orm->exists($key);
	}

	/**
	 * Remove model entity property
	 * 
	 * @param string $key Property key
	 * @return boolean
	 */
	public function remove($key) {
	    $this->_orm->remove($key);
	}

	/**
	 * Force to flag all model entity values as dirty
	 */
	public function forceAllDirty() {
	    $this->_lahaina->logger()->debug('Force all model entity values dirty', $this);
	    $this->_orm->forceAllDirty();
	}

	/**
	 * Set expression to model entity property
	 * 
	 * @param string $key Entity value name
	 * @param mixed $expr SQL expression
	 */
	public function setExpr($key, $expr) {
	    $this->_orm->setExpr($key, $expr);
	}

	/**
	 * Check whether the given values have been changed since this
	 * object was saved
	 * 
	 * @return boolean
	 */
	public function isDirty($property) {
	    return $this->_orm->isDirty($property);
	}

	/**
	 * Check whether the values were the result of a call to create() or not
	 * 
	 * @return boolean
	 */
	public function isNew() {
	    return $this->_orm->isNew();
	}

	/**
	 * Save any entity values which have been modified to the database
	 * 
	 * @return float
	 */
	public function save() {
	    $this->_lahaina->logger()->debug('Save model entity values (' . $this->_orm->id() . ')', $this);
	    return $this->_orm->save();
	}

	/**
	 * Delete model entity from the database
	 * 
	 * @return float
	 */
	public function delete() {
	    $this->_lahaina->logger()->debug('Delete model entity (' . $this->_orm->id() . ')', $this);
	    return $this->_orm->delete();
	}

	/**
	 * Get model entity data
	 * 
	 * @return \lahaina\framework\data\Container
	 */
	public function getData() {
	    return $this->_orm->getData();
	}

	/**
	 * Get the ID
	 * 
	 * @return mixed
	 */
	public function id() {
	    $modelClass = get_class($this);
	    return $this->get($modelClass::ID_COLUMN);
	}

	/**
	 * Get entity name of model class name
	 * 
	 * @return string
	 */
	protected function _getEntityName() {
	    return basename(str_replace('_Model', '', str_replace('\\', '/', get_class($this))));
	}

	/**
	 * Build a foreign key name based on a table name or 
	 * get specific foreign key name
	 * 
	 * @param string $specifiedForeignKeyName
	 * @param string $tableName
	 * @return string
	 */
	protected function _buildForeignKeyName($specifiedForeignKeyName, $tableName) {
	    if (!is_null($specifiedForeignKeyName)) {
		return $specifiedForeignKeyName;
	    }
	    return $tableName . $this->_lahaina->config()->get('framework')->get('foreignKeySuffix');
	}

	/**
	 * Build model class name
	 * 
	 * @param string $name Entity name
	 * @return string
	 */
	protected function _buildModelClassName($name) {
	    return 'application\\models\\' . ucfirst($name) . '_Model';
	}

	/**
	 * Create repository class of model
	 * 
	 * @param string $name Entity name
	 * @return \lahaina\framework\persistence\Orm
	 */
	protected function _createOrmFactory($name) {
	    return $this->_orm->factory($name);
	}

	/**
	 * Construct query for hasOne and hasMany methods
	 * 
	 * @param string $associatedClassName Name of associated class
	 * @param string $foreignKeyName Foreign key name
	 * @param boolean $find Find result
	 * @return mixed
	 */
	protected function _hasOneOrMany($associatedClassName, $foreignKeyName = null, $find = false) {
	    $baseTableName = $this->_orm->getTableName();
	    $foreignKeyName = $this->_buildForeignKeyName($foreignKeyName, $baseTableName);
	    if ($this->exists($foreignKeyName)) {
		$result = $this->_createOrmFactory($associatedClassName)->where($foreignKeyName, $this->id());
		if ($find) {
		    return $result->findAll();
		}
		return $result;
	    }
	    return false;
	}

	/**
	 * Manage one-to-one relations where the foreign key 
	 * is on the associated table
	 * 
	 * @param string $associatedEntityName Name of associated entity
	 * @param string $foreignKeyName Foreign key name
	 * @param boolean $find Find result
	 * @return mixed
	 */
	protected function _hasOne($associatedEntityName, $foreignKeyName = null, $find = false) {
	    return $this->_hasOneOrMany($associatedEntityName, $foreignKeyName, $find);
	}

	/**
	 * Manage one-to-many relations where the foreign key
	 * is on the associated table
	 * 
	 * @param string $associatedEntityName Name of associated entity
	 * @param string $foreignKeyName Foreign key name
	 * @param boolean $find Find result
	 * @return \lahaina\framework\persistence\Orm
	 */
	protected function _hasMany($associatedEntityName, $foreignKeyName = null, $find = false) {
	    return $this->_hasOneOrMany($associatedEntityName, $foreignKeyName, $find);
	}

	/**
	 * Manage one-to-one and one-to-many relations where 
	 * the foreign key is on the base table
	 * 
	 * @param string $associatedEntityName Name of associated entity
	 * @param string $foreignKeyName Foreign key name
	 * @param boolean $find Find result
	 * @return mixed
	 */
	protected function _belongsTo($associatedEntityName, $foreignKeyName = null, $find = false) {
	    $associatedModelClassName = $this->_buildModelClassName($associatedEntityName);
	    $associatedTableName = $associatedModelClassName::TABLE_NAME;

	    $foreignKeyName = $this->_buildForeignKeyName($foreignKeyName, $associatedTableName);

	    if ($this->exists($foreignKeyName)) {
		$associatedEntityId = $this->$foreignKeyName;
		$result = $this->_createOrmFactory($associatedEntityName)->whereIdentfierIs($associatedEntityId);
		if ($find) {
		    return $result->findOne();
		}
		return $result;
	    }
	    return false;
	}

	/**
	 * Manage many-to-many relations via an intermediate model
	 * 
	 * @param string $associatedEntityName Name of associated entity
	 * @param string $joinedEntityName Name of joined entity
	 * @param string $keyToBaseTable Key to base table
	 * @param string $keyToAssociatedTable Key to associated table
	 * @param boolean $find Find result
	 * @return mixed
	 */
	protected function _hasManyThrough($associatedEntityName, $joinedEntityName = null, $keyToBaseTable = null, $keyToAssociatedTable = null, $find = false) {
	    $baseEntityName = basename(str_replace('_Model', '', get_class($this)));

	    // The class name of the join model, if not supplied, is
	    // formed by concatenating the names of the base class
	    // and the associated class, in alphabetical order.
	    if (is_null($joinedEntityName)) {
		$names = array($baseEntityName, $associatedEntityName);
		sort($names, SORT_STRING);
		$joinedEntityName = join("", $names);
	    }

	    $associatedModelClassName = $this->_buildModelClassName($associatedEntityName);
	    $joinedModelClassName = $this->_buildModelClassName($joinedEntityName);

	    // Get table name each entities class
	    $baseModelClassName = get_class($this);
	    $baseTableName = $baseModelClassName::TABLE_NAME;
	    $associatedTableName = $associatedModelClassName::TABLE_NAME;
	    $joinTableName = $joinedModelClassName::TABLE_NAME;

	    // Get ID column name
	    $associatedTableIdColumn = $associatedModelClassName::ID_COLUMN;

	    // Get the column names for each side of the join table
	    $keyToBaseTable = $this->_buildForeignKeyName($keyToBaseTable, $baseTableName);
	    $keyToAssociatedTable = $this->_buildForeignKeyName($keyToAssociatedTable, $associatedTableName);

	    $result = $this->_createOrmFactory($associatedEntityName)
		    ->select("{$associatedTableName}.*")
		    ->join($joinTableName, array("{$associatedTableName}.{$associatedTableIdColumn}", '=', "{$joinTableName}.{$keyToAssociatedTable}"))
		    ->where("{$joinTableName}.{$keyToBaseTable}", $this->id());

	    if ($find) {
		return $result->findAll();
	    }
	    return $result;
	}

	/**
	 * Get model entity property
	 * 
	 * @param mixed $key Property key
	 * @return mixed
	 */
	public function __get($key) {
	    return $this->get($key);
	}

	/**
	 * Set model entity property
	 * 
	 * @param mixed $key Property key
	 * @param mixed $value Property value
	 * @return \lahaina\framework\common\Property
	 */
	public function __set($key, $value) {
	    return $this->set($key, $value);
	}

	/**
	 * Remove model entity property
	 * 
	 * @param mixed $key Property key
	 * @return boolean
	 */
	public function __unset($key) {
	    $this->remove($key);
	}

	/**
	 * Check whether the model entity property exists
	 * 
	 * @param mixed $key Property key
	 * @return boolean
	 */
	public function __isset($key) {
	    return $this->exists($key);
	}

	/**
	 * Call for named get and set methods
	 */
	public function __call($name, $arguments) {
	    if (strpos($name, 'set') === 0) {
		if (!isset($arguments[0])) {
		    $arguments[] = array();
		}
		$name = strtolower(str_replace('set', '', $name));
		return $this->set($name, $arguments[0]);
	    } elseif (strpos($name, 'get') === 0) {
		$name = strtolower(str_replace('get', '', $name));
		return $this->get($name);
	    }
	    throw new FrameworkException('Method not found for model entity');
	}

    }
    