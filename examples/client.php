<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Footstones\RPC\Client;

// $client = new Client('http://localhost/footstones/rpc/example/server.php');
$client = new Client('http://192.168.59.2:10000');

print_result('result1', $client->test1());

function print_result($label, $result)
{
    echo "<p>{$label}:</p>";
    echo "<pre>";
    var_dump($result);
    echo "</pre>";
}