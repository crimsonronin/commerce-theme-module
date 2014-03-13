<?php

putenv("SERVER_TYPE=test");

$applicationRoot = '/../';

chdir(__DIR__ . $applicationRoot);

$loader = require_once('vendor/autoload.php');

$loader->add('Zoop\Theme\Test', __DIR__);
