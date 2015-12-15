<?php

error_reporting(E_ALL);

if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}

require __DIR__ . '/Haf/TestCase.php';

// autoload
$loader = require __DIR__ . '/../vendor/autoload.php';
// $loader->add('Haf\\Test\\', __DIR__);
