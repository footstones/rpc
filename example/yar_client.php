<?php

// var_dump(YAR_ERR_FORBIDDEN);exit();

// $result = serialize(['i' => 1111]);


// var_dump($result);

// exit();


// $result = unserialize('a:4:{s:1:"i";i:1845884547;s:1:"s";i:4;s:1:"o";s:0:"";s:1:"e";s:32:"call to undefined api API::xxx()";}');

// $result = unserialize('a:4:{s:1:"i";i:1855510167;s:1:"s";i:64;s:1:"o";s:15:"this is output.";s:1:"e";a:5:{s:7:"message";s:18:"this is exception.";s:4:"code";i:567;s:4:"file";s:54:"/private/var/www/footstones/rpc/example/yar_server.php";s:4:"line";i:12;s:5:"_type";s:16:"RuntimeException";}}');
// var_dump($result);
// exit();


$client = new Yar_Client("http://localhost/footstones/rpc/example/yar_server.php");
/* the following setopt is optinal */
$client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);

/* call remote service */
$result = $client->xxx("parameter");

var_dump($result);;

