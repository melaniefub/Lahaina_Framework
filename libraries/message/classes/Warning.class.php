<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Warning message extends message class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Warning extends Message {

	protected $type = 'warning';

    }
    