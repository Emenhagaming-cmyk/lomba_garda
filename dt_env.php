<?php

use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Env;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$app->bootstrapWith([
    LoadEnvironmentVariables::class,
    LoadConfiguration::class,
]);

var_dump(Env::get('APP_KEY'));
var_dump(config('app.key'));
