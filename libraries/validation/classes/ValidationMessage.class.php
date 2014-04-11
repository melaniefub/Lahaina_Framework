<?php

    namespace lahaina\libraries\validation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\message\Message;

    /**
     * Validation message extends message class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class ValidationMessage extends Message {

	protected $type = 'error';

	public function __construct($validationErrors, $html_output = null) {

	    $this->text = '<ul>';
	    foreach ($validationErrors as $error) {
		$this->text .= '<li>' . $error . '</li>';
	    }
	    $this->text .= '</ul>';

	    if ($html_output) {
		$this->html_output = $html_output;
	    }
	}

    }
    