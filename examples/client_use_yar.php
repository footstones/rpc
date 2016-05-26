<?php

$client = new Yar_Client('http://127.0.0.1:10000');

$result = $client->echoName('han mei mei');

var_dump($result);;

