<?php

namespace Footstones\RPC;

use \swoole_http_server;

class ServerDaemon
{
    private $server;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function main($op)
    {
        switch ($op) {
            case 'start':
                $this->startServer();
                break;
            case 'stop':
                $this->stopServer();
                break;
            case 'restart':
                $this->restartServer();
                break;
            case 'status':
                $this->statusServer();
            default:
                break;
        }
    }

    protected function startServer()
    {
        $this->server = $this->createHttpServer();
        $this->server->start();
    }

    protected function stopServer()
    {

    }

    protected function restartServer()
    {

    }

    protected function statusServer()
    {
        
    }

    private function createHttpServer()
    {
        $server = new swoole_http_server($this->config['host'], $this->config['port'], SWOOLE_BASE);
        $server->set([
            'http_parse_post' => false
        ]);

        $class = $this->config['handler'];

        $handler = new $class();

        $server->on('request', [$handler, 'onRequest']);

        return $server;
    }
}

