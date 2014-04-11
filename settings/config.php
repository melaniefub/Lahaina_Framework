<?php

    /**
     * Application and framework configuration
     */
    $config['app'] = array(
	'title' => 'Your Application',
	'description' => 'Your application description',
	'url' => 'http://localhost/lahaina',
	'path' => 'C:\dev\Server\XAMPP\htdocs\lahaina',
	'folder' => 'lahaina',
	'version' => '1.0.0',
	'namespace' => 'application',
	'controller' => 'lahaina\framework\mvc\Controller',
	'sessionExpireTime' => 60 * 60 * 24 * 365, // one year
	'start' => array(
	    'controller' => 'Start',
	    'action' => 'index',
	    'identifier' => ''
	),
	'admin' => array(
	    'name' => 'Jonathan Nessier',
	    'email' => 'jonathan.nessier@vtg.admin.ch'
	),
    );

    $config['log'] = array(
	'active' => true,
	'file' => 'C:\dev\application.log',
	'level' => 0, // Error = 3, Warning = 2, Info = 1, Debug = 0
	'filter' => array(
	    '*',
	)
    );

    $config['framework'] = array(
	'foreignKeySuffix' => '_id',
    );

    $config['db'] = array(
	'main' => array(
	    'driver' => 'mysql',
	    'host' => '127.0.0.1',
	    'database' => 'lahaina',
	    'username' => 'user',
	    'password' => '1234',
	    'charset' => 'utf8'
	),
	'optional' => array(),
    );

    $config['libraries'] = array(
	'folder' => 'libraries',
	'load' => array(
	    'message',
	    'security',
	    'navigation',
	    'validation',
	    'tablelist',
	    'mail'
	),
    );

    $config['media'] = array(
	'folder' => 'media',
    );

    $config['template'] = array(
	'folder' => 'templates',
	'name' => 'cdbund',
    );






    