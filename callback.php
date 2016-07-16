<?php

require_once 'vendor/autoload.php';

use Nahid\DribbbleClient\Dribbble;

$api = new Dribbble();
//$api->session->destroy();
echo '<pre>';
 print_r($api->me()->get());
echo '</pre>';