<?php
require __DIR__.'/../vendor/autoload.php';

$helpersPath = __DIR__.'/../global/libs/helpers.php';
if (file_exists($helpersPath)) {
    require $helpersPath;
}