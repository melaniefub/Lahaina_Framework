<?php

    namespace lahaina\framework\exception;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Framework exception extends framework exception class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class ApplicationException extends FrameworkException {

	protected $component = 2;

    }
    