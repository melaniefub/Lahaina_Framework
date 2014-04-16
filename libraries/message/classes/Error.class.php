<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Error message extends message class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Error extends Message {

	protected $type = 'error';

    }
    