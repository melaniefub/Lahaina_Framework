<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use \lahaina\framework\common\Config;

$securityConfig = new Config(array(
	'authenticationType' => 'login', // remote or login
    ));


    