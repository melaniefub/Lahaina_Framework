<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Logger
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Logger {

	/**
	 * @var Config
	 */
	private $_config;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\Config $config Configration of application
	 */
	public function __construct(Config $config) {
	    $this->_config = $config;
	}

	/**
	 * Log debug message
	 * 
	 * @param string $message Log message
	 */
	public function debug($message, $class = null) {
	    $this->_add($message, 0, $class);
	}

	/**
	 * Log info message
	 * 
	 * @param string $message Log message
	 */
	public function info($message, $class = null) {
	    $this->_add($message, 1, $class);
	}

	/**
	 * Log warn message
	 * 
	 * @param string $message Log message
	 */
	public function warn($message, $class = null) {
	    $this->_add($message, 2, $class);
	}

	/**
	 * Log error message
	 * 
	 * @param string $message Log message
	 */
	public function error($message, $class = null) {
	    $this->_add($message, 3, $class);
	}

	/**
	 * Add message to log file
	 * 
	 * @param string $message Log message
	 * @param integer $level Number of log level
	 * @param string $class Name of logging class
	 */
	private function _add($message, $level = 0, $class = null) {
	    if ($this->_config->get('log')->get('active') && $this->_config->get('log')->get('level') <= $level) {
		$date = date('Y-m-d h:i:s');

		switch ($level) {
		    case 3:
			$level = 'ERROR';
			break;
		    case 2:
			$level = 'WARN ';
			break;
		    case 1:
			$level = 'INFO ';
			break;
		    default:
			$level = 'DEBUG';
		}

		$className = get_class($class ? : $this);
		foreach ($this->_config->get('log')->get('filter') as $filter) {
		    if (strpos($className, $filter) !== false || $filter === '*') {
			$file = $this->_config->get('log')->get('file');
			$output = $date . ' | ' . $level . ' | ' . $className . ' - ' . $message;
			file_put_contents($file, $output . "\n", FILE_APPEND);
		    }
		}
	    }
	}

    }
    