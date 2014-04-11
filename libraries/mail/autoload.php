<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

// Register autoload of mail classes
    spl_autoload_register(function($className) {
	$this->_load($className . '.class.php', LIBRARY_PATH . '/mail/classes');
    });
    