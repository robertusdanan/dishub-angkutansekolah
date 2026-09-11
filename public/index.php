<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Bergantung pada Composer autoloader untuk memuat semua kelas yang diperlukan...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel dan tangani request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
