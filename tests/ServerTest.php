<?php

namespace Footstones\RPC\Tests;

use Footstones\RPC\Server;
use Footstones\RPC\Packager;
use Footstones\RPC\Consts;
use Footstones\RPC\Tests\TestService;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessCall()
    {
        $response = $this->callRPC('methodHasNoParameters');

        $this->assertEquals(Consts::ERR_OKEY, $response['body']['s']);
        $this->assertEquals('methodHasNoParameters', $response['body']['r']);
    }

    public function testCallWithParameter()
    {
        $response = $this->callRPC('methodHasParameters', ['p1']);

        $this->assertEquals(Consts::ERR_OKEY, $response['body']['s']);
        $this->assertEquals('methodHasParameters.p1', $response['body']['r']);
    }

    public function testCallWithException()
    {
        $response = $this->callRPC('methodWithException');

        $this->assertEquals(Consts::ERR_EXCEPTION, $response['body']['s']);
        $this->assertEquals('Footstones\RPC\Tests\TestServiceException', $response['body']['e']['_type']);
    }

    public function testCallUndefinedMethod()
    {
        $response = $this->callRPC('undefinedMethod');
        $this->assertEquals(Consts::ERR_REQUEST, $response['body']['s']);
    }


    private function callRPC($method, $parameters = [])
    {
        $server = new Server(new TestService());

        $input = Packager::pack(['m' => $method, 'p' => $parameters]);

        $response = $server->handle($input['data']);

        return Packager::unpack($response['data']);
    }


}
