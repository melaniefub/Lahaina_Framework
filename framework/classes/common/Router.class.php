<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use \lahaina\framework\data\Container;
use \lahaina\framework\http\Uri;

    /**
     * Router
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Router {

	/**
	 * @var Uri
	 */
	private $_uri;

	/**
	 * @var Lahaina
	 */
	private $_lahaina;
	private $_path = '';

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @param array $uri Parts of uri as array for routing
	 */
	public function __construct(Lahaina $lahaina, Uri $uri = null) {

	    $this->_lahaina = $lahaina;
	    $this->_path = PATH . '/application/classes/controllers/';

	    if ($uri === null) {
		$this->_uri = $this->_lahaina->request()->getUri();
	    } else {
		$this->_uri = $uri;
	    }
	}

	/**
	 * Route to controller and action
	 */
	public function route() {

	    $controllerFolder = '';
	    $controllerName = $this->_lahaina->config('app.start.controller');
	    $controllerClassName = $this->_lahaina->config('app.namespace') . '\\controllers\\' . ucfirst($controllerName) . '_Controller';
	    $actionName = $this->_lahaina->config('app.start.action');
	    $identifier = $this->_lahaina->config('app.start.identifier');

	    $index = 0;
	    foreach ($this->_uri as $uriPart) {
		$path = $this->_path . $controllerFolder . $uriPart;
		if (file_exists($path) && $index != $this->_uri->count() - 1) {
		    $controllerFolder .= ($uriPart == '' ? '' : $uriPart . '/');
		    $index++;
		} else {
		    break;
		}
	    }

	    if ($this->_uri->exists($index) && $this->_uri->get($index) != '') {
		$class = $this->_lahaina->config('app.namespace') . '\\controllers\\' . str_replace('/', '\\', $controllerFolder) . ucfirst($this->_uri->get($index)) . '_Controller';
		if (class_exists($class)) {
		    $controllerName = ucfirst($this->_uri->get($index));
		    $controllerClassName = $this->_lahaina->config('app.namespace') . '\\controllers\\' . str_replace('/', '\\', $controllerFolder) . ucfirst($this->_uri->get($index)) . '_Controller';
		    $index++;
		}
		if ($this->_uri->exists($index) && $this->_uri->get($index) != '') {
		    if (method_exists($controllerClassName, $this->_uri->get($index))) {
			$actionName = $this->_uri->get($index);
			$index++;
		    }
		}
		if ($this->_uri->exists($index)) {
		    $identifier = $this->_uri->get($index);
		}
	    }

	    $this->_lahaina->setCurrentController($controllerClassName);
	    $controller = new $controllerClassName($this->_lahaina);

	    if (is_callable(array($controller, $actionName))) {

		// Set current
		$this->_lahaina->setCurrentAction($actionName);

		// Route
		$this->_lahaina->logger()->info('Route URI (' . $this->_uri->getUriAsString() . ')');

		if ($identifier != '') {
		    $this->_lahaina->setCurrentIdentifier($identifier);
		    return $controller->{$actionName}($identifier);
		}
		return $controller->{$actionName}();
	    } else {

		// Set current
		$this->_lahaina->setCurrentAction('error');

		// Route
		$this->_lahaina->logger()->warn('Requested URI (' . $this->_uri->getUriAsString() . ') not found', $this);
		return $controller->actionNotFound();
	    }
	}

    }
    