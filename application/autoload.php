<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    // Register autoload of manual definied application classes
    spl_autoload_register(function($className) {
	$this->_load($className . '.class.php', __DIR__ . '/classes/controllers/admin');
    });














    