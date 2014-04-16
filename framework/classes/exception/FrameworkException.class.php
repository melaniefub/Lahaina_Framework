<?php

    namespace lahaina\framework\exception;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Framework exception extends exception class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class FrameworkException extends \Exception {

	protected $component = 0;

	/**
	 * Constructor 
	 * 
	 * @param string $message Message
	 */
	public function __construct($message) {
	    $this->message = $message;
	}

	/**
	 * Get component as string
	 * 
	 * @return string Component
	 */
	public function getComponentAsString() {
	    switch ($this->component) {
		case 1:
		    return 'Library';
		case 2:
		    return 'Application';
		default:
		    return 'Framework';
	    }
	}

    }
    