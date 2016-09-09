<?php

#use \Api\Noip;
require_once('NoIP/NoIP.php');

$noIp = new Noip('login','password');

$result = $noIp->refreshHosts();

print_r( $result );
