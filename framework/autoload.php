<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

// Register autoload of common classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/common/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of exception classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/exception/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of model-view-controller classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/mvc/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of http classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/http/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of persistence classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/persistence/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of handler classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/handler/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of data classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/data/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });

// Register autoload of view classes
    spl_autoload_register(function ($class) {
	$classFile = PATH . '/framework/classes/view/' . basename(str_replace('\\', '/', $class)) . '.class.php';
	if (file_exists($classFile)) {
	    require_once ($classFile);
	}
    });
    