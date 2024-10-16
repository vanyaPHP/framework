<?php

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../vendor/autoload.php';

use VanyaPhp\CustomFramework\Foundation\Application;

$app = new Application();

$cities = \Models\City::all(['city_name', 'addresses.user.posts:title']);

return $app;