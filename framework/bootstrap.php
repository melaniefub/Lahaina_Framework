<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\http\Request;
use lahaina\framework\http\PostRequest;
use lahaina\framework\common\Lahaina;
use lahaina\framework\common\Loader;
use lahaina\framework\common\Router;
use lahaina\framework\common\Config;
use lahaina\framework\common\Logger;

// Create configuration
    $config = new Config($config);

    // Start session
    if ($config->get('app')->get('sessionExpireTime')) {
	session_set_cookie_params($config->get('app')->get('sessionExpireTime'));
    }
    session_start();

    // Create logger
    $logger = new Logger($config);

    // Read HTTP request
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $uri = array_values(array_diff($uri, array($config->get('app.folder'), '')));

    // Check method and create HTTP request
    if ($method == 'GET') {
	$request = new Request($_GET, $uri);
    } else {
	$request = new PostRequest($_GET, $_POST, $_FILES, $uri);
    }

    // Create lahaina framework base
    $lahaina = new Lahaina($request, $config, $logger);
    $lahaina->setData('startTime', $startTime);

    // Create and execute router
    $router = new Router($lahaina);
    $router->route(); // Routing