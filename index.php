<?php
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
set_time_limit(30);

ini_set('display_errors', 1);
ini_set("log_errors", 1);

if(ini_get('display_errors')==0)
	ini_set("error_log", __DIR__.'/php_errors.log');


define('ROOT', __DIR__);
define('WORKSPACE', __DIR__.'/php-workspace');
define('TEMPLATES', __DIR__.'/php-workspace/twigs');

require_once 'loader.php';