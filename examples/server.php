<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Footstones\RPC\Server;

class TestService
{
    public function test1()
    {
        echo 'this is output';
        return ['result' => 'this is test1 result.'];
    }
}

$server = new Server();
$server->setService(new TestService());

$response = $server->handle(file_get_contents('php://input'));

echo $response['data'];