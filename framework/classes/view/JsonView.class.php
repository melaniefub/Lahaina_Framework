<?php

    namespace lahaina\framework\view;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\View;

    /**
     * JSON view extends view class
     *
     * @version 1.0.0
     *
     * @author Jonathan Nessier
     */
    class JsonView extends View {

	public function render() {
	    if (is_string($this->_lahaina->getData('json'))) {
		echo $this->_lahaina->getData('json');
	    }
	}

	public function getPath() {
	    return true;
	}

    }
    