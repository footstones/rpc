<?php

namespace Footstones\RPC\Examples;

use Footstones\RPC\ServerHandler;
use Footstones\RPC\Server;

class ExampleServerHandler implements ServerHandler
{
    public function onRequest($request, $response)
    {
        $server = new Server($this->getLogger('ServerHandler'));
        $handled = $server->handle(new TestService(), $request->rawContent(), $request->server);
        $response->end($handled['data']);
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    protected function getLogger($channel = null)
    {
        return $this->server->getLogger($channel);
    }


}

class TestService
{
    public function echoName($name)
    {
        return ['result' => "Your name: {$name}."];
    }
}