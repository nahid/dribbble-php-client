<?php

require 'vendor/autoload.php';
use Nahid\DribbbleClient\Dribbble;

$api = new Dribbble();
echo $api->makeAuthLink('Login');
