<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use \lahaina\framework\data\Container;

    /**
     * Configration
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Config extends Container {

	/**
	 * Constructor
	 * 
	 * @param array $data Container data as array
	 * @throws FrameworkException
	 */
	public function __construct($data = array()) {
	    $this->_className = get_class($this);

	    foreach ($data as $key => $value) {
		parent::set($key, $value);
	    }
	}

	public function set($key, $value = array()) {
	    throw new FrameworkException('Directly set of config property is not allowed');
	}

    }
    