<?php

    // Set start time for measuring php load time
    $startTime = microtime(true);

    // Include settings files
    require_once (dirname(__FILE__) . '/settings/config.php');
    require_once (dirname(__FILE__) . '/settings/constant.php');

    // Require framework classes
    require_once(dirname(__FILE__) . '/framework/autoload.php');

    // Initialize framework
    require_once(dirname(__FILE__) . '/framework/bootstrap.php');
    