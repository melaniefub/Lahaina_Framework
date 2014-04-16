<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Info message extends message class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Info extends Message {

	protected $type = 'info';

    }
    