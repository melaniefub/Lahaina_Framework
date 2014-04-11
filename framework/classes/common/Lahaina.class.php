<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\http\Request;
use lahaina\framework\persistence\Database;
use lahaina\framework\persistence\Orm;
use lahaina\framework\handler\Error;
use lahaina\framework\data\Container;
use lahaina\framework\exception\FrameworkException;
use \lahaina\framework\common\Loader;

    /**
     * Lahaina framework base class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Lahaina {

	/**
	 * @var Container
	 */
	private $_data;

	/**
	 * @var Loader 
	 */
	private $_loader;

	/**
	 * @var Config
	 */
	private $_config;
	private $_dbConnections = array();

	/**
	 * @var Session
	 */
	private $_session;

	/**
	 * @var Logger
	 */
	private $_logger;

	/**
	 * @var Request 
	 */
	private $_request;
	private $_ormConnections = array();

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\http\Request $request
	 * @param \lahaina\framework\common\Config $config
	 * @param \lahaina\framework\common\Logger $logger Logger
	 */
	public function __construct(Request $request, Config $config, Logger $logger) {

	    $this->_logger = $logger;
	    $this->_config = $config;
	    $this->_request = $request;

	    $this->logger()->debug('Create Lahaina Framework object', $this);

	    // Create and set error handler
	    $errorHandler = new Error($this);
	    $errorHandler->setError();
	    $errorHandler->setException();

	    // Create lahaina data
	    $this->_data = new Container(array('app' => array(), 'current' => array()));

	    // Create Session
	    $this->_session = new Session($this);

	    // Create Loader
	    $this->_loader = new Loader($this);
	}

	/**
	 * Get application configuration
	 * 
	 * @param string $key Config key
	 * @return \lahaina\framework\common\Config
	 */
	public function config($key = null) {
	    if ($key) {
		return $this->_config->get($key);
	    }
	    return $this->_config;
	}

	/**
	 * Get database connection
	 * 
	 * @param string $connectionName Connection name
	 * @return \lahaina\framework\persistence\Database
	 */
	public function database($connectionName = 'main') {
	    if (isset($this->_dbConnections[$connectionName])) {
		return $this->_dbConnections[$connectionName];
	    } elseif ($this->_config->get('db')->exists($connectionName)) {
		$this->_dbConnections[$connectionName] = new Database($connectionName, $this->_config->get('db')->get($connectionName), $this->_logger);
		return $this->_dbConnections[$connectionName];
	    }
	    throw new FrameworkException('No or unknown database connection (' . $connectionName . ') definied');
	}

	/**
	 * Get object relation mapper
	 * 
	 * @param string $connectionName Connection name
	 * @return \lahaina\framework\persistence\Orm
	 */
	public function orm($connectionName = 'main') {
	    if (isset($this->_ormConnections[$connectionName])) {
		return $this->_ormConnections[$connectionName];
	    } elseif ($this->_config->get('db')->exists($connectionName)) {
		$this->_ormConnections[$connectionName] = new Orm($this, $connectionName);
		return $this->_ormConnections[$connectionName];
	    }
	    throw new FrameworkException('No or unknown database connection (' . $connectionName . ') for object relation mapper definied');
	}

	/**
	 * Get session
	 * 
	 * @return \lahaina\framework\common\Session
	 */
	public function session() {
	    return $this->_session;
	}

	/**
	 * Get loader
	 * 
	 * @return \lahaina\framework\common\Loader
	 */
	public function loader() {
	    return $this->_loader;
	}

	/**
	 * Get logger
	 * 
	 * @return \lahaina\framework\common\Logger
	 */
	public function logger() {
	    return $this->_logger;
	}

	/**
	 * Get request
	 * 
	 * @return \lahaina\framework\http\Request
	 */
	public function request() {
	    return $this->_request;
	}

	/**
	 * Get data property
	 * 
	 * @param string $key Data property key
	 * @return mixed
	 */
	public function getData($key = null) {
	    if ($key === null) {
		return $this->_data->get('app');
	    } elseif ($this->_data->get('app')->exists($key)) {
		return $this->_data->get('app')->get($key);
	    }
	}

	/**
	 * Set data property
	 * 
	 * @param mixed $key Data property key
	 * @param mixed $value Value
	 * @return mixed
	 */
	public function setData($key, $value) {
	    return $this->_data->get('app')->set($key, $value);
	}

	/**
	 * Set name of current identifier
	 * 
	 * @param string $name Identifier
	 */
	public function setCurrentIdentifier($name) {
	    $this->_setCurrent('identifier', $name);
	}

	/**
	 * Set name of current action
	 * 
	 * @param string $name Name of action
	 */
	public function setCurrentAction($name) {
	    $this->_setCurrent('action', $name);
	}

	/**
	 * Set name of current controller
	 * 
	 * @param string $name Name of controller
	 */
	public function setCurrentController($name) {
	    $this->_setCurrent('controller', $name);
	}

	/**
	 * Get current identifier
	 * 
	 * @return string Name of action
	 */
	public function getCurrentIdentifier() {
	    return $this->_getCurrent('identifier');
	}

	/**
	 * Get name of current action
	 * 
	 * @return string Name of action
	 */
	public function getCurrentAction() {
	    return $this->_getCurrent('action');
	}

	/**
	 * Get name of current controller
	 * 
	 * @return string Name of controller
	 */
	public function getCurrentController() {
	    return $this->_getCurrent('controller');
	}

	/**
	 * Check wether action is current
	 * 
	 * @param string|array $names Name(s) of action
	 * @return boolean
	 */
	public function hasCurrentAction($names) {
	    if (is_array($names)) {
		foreach ($names as $name) {
		    if ($this->_hasCurrentAction($name)) {
			return true;
		    }
		}
	    } elseif (is_string($names)) {
		return $this->_hasCurrentAction($names);
	    }
	    return false;
	}

	/**
	 * Check wether controller is current
	 * 
	 * @param string|array $names Name(s) of controller
	 * @return boolean
	 */
	public function hasCurrentController($names) {
	    if (is_array($names)) {
		foreach ($names as $name) {
		    if ($this->_hasCurrentController($name)) {
			return true;
		    }
		}
	    } elseif (is_string($names)) {
		return $this->_hasCurrentController($names);
	    }
	    return false;
	}

	/**
	 * Check wether action is current
	 * 
	 * @return string Name of action
	 */
	private function _hasCurrentAction($name) {
	    return ($this->_getCurrent('action') === $name);
	}

	/**
	 * Check if controller is current
	 * 
	 * @return string Name of action
	 */
	private function _hasCurrentController($name) {
	    $name = strtolower(str_replace('/', '\\', $name));
	    $controller = strtolower($this->_getCurrent('controller'));
	    $name = str_replace('/', '\\', $name);
	    if ($controller != '') {
		return (strpos($controller, $name) !== false);
	    } 
	    return false;
	}

	/**
	 * Set current name of controller or action
	 *
	 * @param string $type Type
	 * @param string $name Name or identifier
	 * @throws FrameworkException
	 */
	private function _setCurrent($type, $name) {
	    if (!is_array($name)) {
		$this->_data->get('current')->set($type, $name);
	    } else {
		throw new FrameworkException('Current name or value cannot be an array');
	    }
	}

	/**
	 * Get current name of controller or action
	 *
	 * @param string $type Action or Controller
	 * @return string Name of controller and action
	 */
	private function _getCurrent($type) {
	    if ($this->_data->get('current')->exists($type)) {
		return $this->_data->get('current')->get($type);
	    }
	    return null;
	}

    }
    