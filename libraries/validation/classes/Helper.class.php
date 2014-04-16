<?php

    namespace lahaina\libraries\validation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;

    /**
     * Helper for message library
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Helper {

	/**
	 * Check wether named value has validation error
	 * 
	 * @param mixed $name Name(s) of value(s)
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @param string $errorCssClass CSS class for validation error
	 * 
	 * @return string
	 */
	public function checkValidationError($names, Lahaina $lahaina, $errorCssClass = 'validation-error') {
	    if (is_string($names)) {
		$names = array($names);
	    }
	    foreach ($names as $name) {
		if ($lahaina->getData()->exists('validationErrors')) {
		    $errors = $lahaina->getData('validationErrors');
		    if ($errors->exists($name)) {
			return $errorCssClass;
		    }
		}
	    }
	    return '';
	}

    }
    