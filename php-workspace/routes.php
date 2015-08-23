<?php
// Create Slim app
$app = App::getInstance()->slim;

// Define named route
$app->get('/', function(){  })->setName('root');

// Run app
$app->run();