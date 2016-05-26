<?php

namespace Footstones\RPC\Tests;

use Footstones\RPC\Server;
use Footstones\RPC\Packager;
use Footstones\RPC\Consts;
use Footstones\RPC\Tests\TestService;
use Footstones\RPC\Client;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    // public function testEchoName()
    // {
    //     $result = $this->getTestService()->echoName('li lei');

    //     var_dump($result);
    // }

    public function testEchoNameWithYar()
    {
        $result = $this->getYarTestService()->echoName('li lei');

        var_dump($result);
    }
    
    protected function getTestService()
    {
        return new Client('http://127.0.0.1:10000');
    }

    protected function getYarTestService()
    {
        return new \Yar_Client('http://127.0.0.1:10000');
    }

}
