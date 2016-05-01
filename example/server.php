<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Footstones\RPC\Server;

class TestService
{
    public function test()
    {
        throw new \RuntimeException('this is exception', 567);
        return ['hello' => 'world'];
    }
}

$server = new Server(new TestService());

$response = $server->handle(file_get_contents('php://input'));

echo $response['data'];