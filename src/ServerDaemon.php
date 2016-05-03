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
            case 'reload':
                $this->reloadServer();
                break;
            case 'status':
                $this->statusServer();
                break;
            default:
                break;
        }
    }

    protected function startServer()
    {
        if (file_exists($this->config['pid_file'])) {
            $pid = file_get_contents($this->config['pid_file']);

            if (posix_kill($pid, 0)) {
                exit("Server is already running.\n");
            }
        }

        $this->server = $this->createHttpServer();
        $this->server->start();
    }

    protected function stopServer()
    {
        $pid = file_get_contents($this->config['pid_file']);
        posix_kill($pid, SIGTERM);
    }

    protected function restartServer()
    {
        $this->stopServer();
        $this->startServer();
    }

    protected function reloadServer()
    {
        $pid = file_get_contents($this->config['pid_file']);
        posix_kill($pid, SIGUSR1);
    }

    protected function statusServer()
    {

    }

    private function createHttpServer()
    {
        $server = new swoole_http_server($this->config['host'], $this->config['port'], SWOOLE_BASE);
        $server->set([
            'http_parse_post' => false,
            'daemonize' => empty($this->config['daemonize']) ? 0 : 1,
            'worker_num' => empty($this->config['worker_num']) ? 4 : $this->config['worker_num'],
        ]);

        $class = $this->config['handler'];

        $handler = new $class();

        $server->on('Start', array($this, 'onMasterStart'));
        $server->on('Shutdown', array($this, 'onMasterStop'));
        $server->on('ManagerStart', array($this, 'onManagerStart'));
        $server->on('WorkerStart', array($this, 'onWorkerStart'));

        $server->on('request', [$handler, 'onRequest']);

        return $server;
    }

    public function onMasterStart($server)
    {
        swoole_set_process_name(sprintf('%s: master (%s:%s,%s)', $this->getServerName(), $this->config['host'], $this->config['port'], $this->config['config_file']));
        file_put_contents($this->config['pid_file'], $server->master_pid);
    }

    public function onMasterStop($serv)
    {
        unlink($this->config['pid_file']);
    }

    public function onManagerStart($server)
    {
        swoole_set_process_name(sprintf('%s: manager', $this->getServerName()));
    }

    public function onWorkerStart($server)
    {
        swoole_set_process_name(sprintf('%s: worker (under master #%s)', $this->getServerName(), $server->master_pid));
    }

    protected function getServerName()
    {
        return !empty($this->config['server_name']) ? $this->config['server_name'] : 'footstone server';
    }

}

