<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;

    /**
     * Loader for libraries, model and additional classes of the application
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Loader {

	/**
	 * @var Lahaina 
	 */
	private $_lahaina;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	    $this->_lahaina->logger()->debug('Create loader', $this);

	    // Load configured libraries
	    $libraries = $lahaina->config()->get('libraries.load');
	    foreach ($libraries as $libraryName) {
		$this->library($libraryName, $lahaina);
	    }

	    // Register autoload for application classes, models and controller
	    spl_autoload_register(function($className) {
		$this->_load($className . '.class.php', PATH . '/application/classes/common');
		$this->_load($className . '.class.php', PATH . '/application/classes/models');
		$this->_load($className . '.class.php', PATH . '/application/classes/controllers');
	    });

	    // Load manual added application classes 
	    if (file_exists(PATH . '/application/autoload.php')) {
		require_once (PATH . '/application/autoload.php');
	    } else {
		throw new FrameworkException('Cannot find the autoload file (' . PATH . '/application/autoload.php) for manual definied application classes');
	    }
	}

	/**
	 * Load library
	 * 
	 * @param string $name Name of library
	 * @throws FrameworkException
	 */
	public function library($name) {
	    $this->_lahaina->logger()->debug('Load library (' . $name . ')', $this);
	    if (file_exists(LIBRARY_PATH . $name . '/autoload.php')) {
		require_once (LIBRARY_PATH . $name . '/autoload.php');
	    } else {
		throw new FrameworkException('Cannot find the autoload file (autoload.php) for the ' . $name . ' library', 4);
	    }

	    if (file_exists(LIBRARY_PATH . $name . '/config.php')) {
		require_once (LIBRARY_PATH . $name . '/config.php');
	    }

	    if (file_exists(LIBRARY_PATH . $name . '/bootstrap.php')) {
		require_once (LIBRARY_PATH . $name . '/bootstrap.php');
	    }
	}

	/**
	 * Load function for classes
	 * 
	 * @param string $className Name of class
	 * @param string $classPath Path to class
	 * @return boolean
	 */
	private function _load($className, $classPath) {
	    $classFile = $classPath . '/' . basename(str_replace('\\', '/', $className));
	    if (file_exists($classFile)) {
		$this->_lahaina->logger()->debug('Load class (' . $className . ')', $this);
		require_once ($classFile);
		return true;
	    }
	    return false;
	}

    }

?>
