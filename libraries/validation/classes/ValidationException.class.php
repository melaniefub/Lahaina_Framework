<?php

    namespace lahaina\libraries\validation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Validation exception
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class ValidationException extends \Exception {

	protected $errors = array();

	public function __construct($message, array $errors = array()) {
	    parent::__construct($message);
	    $this->errors = $errors;
	}

	public function getErrors() {
	    return $this->errors;
	}

    }
    