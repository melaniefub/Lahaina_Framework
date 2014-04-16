<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Success message extends message class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Success extends Message {

	protected $type = 'success';

    }
    