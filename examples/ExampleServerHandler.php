<?php

namespace Footstones\RPC\Examples;

use Footstones\RPC\Server;

class ExampleServerHandler
{
    public function onRequest($request, $response)
    {
        $server = new Server(new TestService());
        $handled = $server->handle($request->rawContent());
        $response->end($handled['data']);
    }
}

class TestService
{
    public function test1()
    {
        echo 'this is output';
        return ['result' => 'this is test1 result.'];
    }
}