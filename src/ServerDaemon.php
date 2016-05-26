<?php

namespace Footstones\RPC;

use \swoole_http_server;

class ServerDaemon
{
    protected $config;

    protected $server;

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

        foreach ($this->config['processes'] as $name => $process) {
            $process = new $process($this->server, $this->getServerName(), $name);
            $this->server->addProcess($process->getProcess());
        }

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
        $server = new swoole_http_server($this->config['host'], $this->config['port']);

        $cfg = [];
        $cfg['http_parse_post'] = false;
        $cfg['daemonize'] =  empty($this->config['daemonize']) ? 0 : 1;
        $cfg['worker_num'] = empty($this->config['worker_num']) ? 4 : $this->config['worker_num'];
        $cfg['dispatch_mode'] = 3;
        if ($this->config['error_log']) {
            $cfg['log_file'] = $this->config['error_log'];
        }

        $server->set($cfg);

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
        swoole_set_process_name(sprintf('%s: manager [> #%s]', $this->getServerName(), $server->master_pid));
    }

    public function onWorkerStart($server)
    {
        swoole_set_process_name(sprintf('%s: worker [> #%s]', $this->getServerName(), $server->master_pid));
    }

    protected function getServerName()
    {
        return !empty($this->config['server_name']) ? $this->config['server_name'] : 'footstone server';
    }

}

