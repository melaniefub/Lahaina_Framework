<?php

    /**
     * Application and framework constants
     */
    define('PATH', $config['app']['path']);
    define('URL', $config['app']['url']);

    define('TEMPLATE_PATH', PATH . '/' . $config['template']['folder'] . '/' . $config['template']['name'] . '/');
    define('TEMPLATE_URL', URL . '/' . $config['template']['folder'] . '/' . $config['template']['name'] . '/');

    define('LIBRARY_PATH', PATH . '/' . $config['libraries']['folder'] . '/');
    define('LIBRARY_URL', URL . '/' . $config['libraries']['folder'] . '/');

    define('MEDIA_PATH', PATH . '/' . $config['media']['folder'] . '/');
    define('MEDIA_URL', URL . '/' . $config['media']['folder'] . '/');
    