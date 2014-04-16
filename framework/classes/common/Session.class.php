<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\data\Container;
use lahaina\framework\common\Lahaina;

    /**
     * Session
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Session {

	/**
	 * @var Helper
	 */
	private $_helper;

	/**
	 * @var Lahaina
	 */
	private $_lahaina;
	private $_flashData;
	private $_flashDataNew;
	private $_data;

	/**
	 * Constructor
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	    $this->_lahaina->logger()->debug('Create session', $this);

	    if (!isset($_SESSION['flash'])) {
		$this->_flashData = new Container();
	    } else {
		$this->_flashData = new Container($_SESSION['flash']);
	    }
	    $this->_flashDataNew = new Container();

	    if (!isset($_SESSION['app'])) {
		$this->_data = new Container();
	    } else {
		$this->_data = new Container($_SESSION['app']);
	    }

	    $this->_helper = new Helper();
	}

	/**
	 * Set a flash value with given key
	 *
	 * @param string $key Key of flash value
	 * @param mixed $value Flash value
	 */
	public function setFlash($key, $value) {
	    if (is_object($value)) {
		$value = serialize($value);
	    }
	    $this->_flashDataNew->set($key, $value);
	}

	/**
	 * Get multiple flash values or a single flash value with given key
	 *
	 * @param string $key Key of flash value
	 * @return mixed
	 */
	public function getFlash($key = null) {
	    if (isset($key)) {
		if ($this->_flashData->exists($key)) {

		    if ($this->_helper->isSerialized($this->_flashData->get($key))) {
			return unserialize($this->_flashData->get($key));
		    }
		    return $this->_flashData->get($key);
		}
		return null;
	    } else {
		return $this->_flashData;
	    }
	}

	/**
	 * Destructor, saving data to session before the class will be destroyed
	 */
	public function __destruct() {
	    $_SESSION['app'] = $this->_data->toArray();
	    $_SESSION['flash'] = $this->_flashDataNew->toArray();
	}

	/**
	 * Set session value
	 *
	 * @param mixed $name Name or index of value
	 * @param mixed $value Value to set
	 * @return mixed
	 */
	public function set($name, $value) {
	    if (is_object($value)) {
		$value = serialize($value);
	    }
	    return $this->_data->set($name, $value);
	}

	/**
	 * Remove session property
	 * 
	 * @param mixed $name Property key
	 * @return boolean
	 */
	public function remove($name) {
	    return $this->_data->remove($name);
	}

	/**
	 * Get session value 
	 *
	 * @param mixed $name Name of value
	 * @return mixed
	 */
	public function get($name) {
	    if ($this->_helper->isSerialized($this->_data->get($name))) {
		return unserialize($this->_data->get($name));
	    }
	    return $this->_data->get($name);
	}

	/**
	 * Check if session value exists
	 * 
	 * @param mixed $name Name of value
	 * @return boolean
	 */
	public function exists($name) {
	    return $this->_data->exists($name);
	}

    }
    