<?php

    namespace lahaina\framework\http;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use \lahaina\framework\data\Container;

    /**
     * Uri
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Uri extends Container {

	/**
	 * Get URI as String
	 * 
	 * @return string
	 */
	public function getUriAsString() {
	    $uriAsString = '';
	    foreach ($this->_container as $uri) {
		if ($uri != '') {
		    $uriAsString .= '/' . $uri;
		}
	    }
	    return $uriAsString;
	}

    }
    