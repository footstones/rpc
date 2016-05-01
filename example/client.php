<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Footstones\RPC\Client;

$client = new Client('http://localhost/footstones/rpc/example/server.php');

$result = $client->call('test');

var_dump($result);