<?php

    namespace lahaina\framework\data;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;

    /**
     * One- and multi-dimensional data container
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Container implements \IteratorAggregate, \Countable {

	/**
	 * @var string
	 */
	protected $_className;

	/**
	 * @var array
	 */
	protected $_container = array();

	/**
	 * Constructor
	 * 
	 * @param array $data Container data as array
	 * @throws FrameworkException
	 */
	public function __construct($data = array()) {
	    $this->_className = get_class($this);

	    if (!is_array($data)) {
		throw new FrameworkException('Data must be an array');
	    }

	    foreach ($data as $key => $value) {
		$this->set($key, $value);
	    }
	}

	/**
	 * Output the container data as a multidimensional array
	 *
	 * @return array
	 */
	public function toArray() {
	    $data = $this->_container;
	    foreach ($data as $key => $value) {
		if ($value instanceof $this->_className) {
		    $data[$key] = $value->toArray();
		}
	    }
	    return (array) $data;
	}

	/**
	 * Set container property
	 * 
	 * @param mixed $key Property key
	 * @param mixed $value Property value
	 * @return \lahaina\framework\data\Container
	 * @throws FrameworkException
	 */
	public function set($key, $value = null) {
	    if ($key === null) {
		throw new FrameworkException('Cannot not set NULL as name of value');
	    }

	    if (is_array($value)) {
		$value = new $this->_className($value);
	    }

	    if ($this->exists($key) && $value instanceof self && count($value->_container) === 0) {
		return $this->get($key);
	    }
	    $this->_container[$key] = $value;
	    return $this;
	}

	/**
	 * Check whether the container property key exists
	 * 
	 * @param mixed $key Property key
	 * @return boolean
	 */
	public function exists($key) {
	    return isset($this->_container[$key]);
	}

	/**
	 * Remove container property
	 * 
	 * @param mixed $key Property key
	 * @return boolean
	 */
	public function remove($key) {
	    unset($this->_container[$key]);
	}

	/**
	 * Get container property
	 *
	 * @param mixed $key Property key
	 * @return mixed
	 */
	public function get($key) {
	    if (isset($this->_container[$key])) { // as model property
		return $this->_container[$key];
	    } elseif (strpos($key, '.') !== false) { // as related model entity property
		$keys = explode('.', $key, 2);
		return $this->get($keys[0])->get($keys[1]);
	    }
	    return null;
	}

	/**
	 * Number of container properties, implements Countable
	 * 
	 * @return integer
	 */
	public function count() {
	    return count($this->_container);
	}

	/**
	 * Check wether the container property has value
	 * 
	 * @param mixed $value Property value
	 * @return boolean
	 */
	public function has($value) {
	    return in_array($value, $this->_container);
	}

	/**
	 * Get an iterator, implements IteratorAggregate
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
	    return new \ArrayIterator($this->_container);
	}

	/**
	 * Get container property
	 * 
	 * @param mixed $key Property key
	 * @return mixed
	 */
	public function __get($key) {
	    return $this->get($key);
	}

	/**
	 * Set container property
	 * 
	 * @param mixed $key Property key
	 * @param mixed $value Property value
	 * @return \lahaina\framework\data\Container
	 */
	public function __set($key, $value) {
	    return $this->set($key, $value);
	}

	/**
	 * Remove container property
	 * 
	 * @param mixed $key Property key
	 * @return boolean
	 */
	public function __unset($key) {
	    $this->remove($key);
	}

	/**
	 * Check whether the container property exists
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
	    } elseif (strpos($name, 'set') === 0) {
		$name = strtolower(str_replace('get', '', $name));
		return $this->get($name);
	    }
	    throw new FrameworkException('Method not found');
	}

    }
    