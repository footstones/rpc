<?php

namespace Footstones\RPC;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \swoole_http_server;

class ServerDaemon
{
    protected $config;

    protected $server;

    protected $loggers = [];

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
            default:
                break;
        }
    }

    protected function startServer()
    {
        $pid = $this->getPidFromFile();
        if ($pid && posix_kill($pid, 0)) {
            exit("Server is already running.\n");
        }

        print("start server.\n");

        $this->server = $this->createHttpServer();

        foreach ($this->config['processes'] as $name => $process) {
            $process = new $process($this, $name);
            $this->server->addProcess($process->getProcess());
        }

        $this->server->start();
    }

    protected function getPidFromFile()
    {
        if (file_exists($this->config['pid_file'])) {
            $pid = (int) file_get_contents($this->config['pid_file']);
        } else {
            $pid = 0;
        }

        return $pid;
    }

    protected function stopServer()
    {
        $pid = $this->getPidFromFile();
        if (empty($pid)) {
            exit('server is not running.');
        }

        print("stop server.\n");

        posix_kill($pid, SIGTERM);
    }

    protected function restartServer()
    {
        $this->stopServer();

        $i = 0;
        while ($i < 5) {
            $pid = $this->getPidFromFile();
            if (empty($pid)) {
                break;
            }
            sleep(1);
            $i++;
        }

        $this->startServer();
    }

    protected function reloadServer()
    {
        $pid = $this->getPidFromFile();
        if (empty($pid)) {
            exit('server is not running.');
        }

        print("reload server.\n");

        posix_kill($pid, SIGUSR1);

        $this->getLogger()->notice("reload server, pid #{$pid}.");
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
        $handler->setServer($this);

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

        $this->getLogger()->notice("server started, pid #{$server->master_pid}.");
    }

    public function onMasterStop($server)
    {
        if (file_exists($this->config['pid_file'])) {
            unlink($this->config['pid_file']);
        }

        $this->getLogger()->notice("server stoped, pid #{$server->master_pid}.");
    }

    public function onManagerStart($server)
    {
        swoole_set_process_name(sprintf('%s: manager [> #%s]', $this->getServerName(), $server->master_pid));
    }

    public function onWorkerStart($server)
    {
        swoole_set_process_name(sprintf('%s: worker [> #%s]', $this->getServerName(), $server->master_pid));
    }

    public function getServerName()
    {
        return !empty($this->config['server_name']) ? $this->config['server_name'] : 'footstone server';
    }

    public function getServerPid()
    {
        return $this->server->master_pid;
    }

    public function getLogger($channel = 'default')
    {
        if (isset($this->loggers[$channel])) {
            return $this->loggers[$channel];
        }

        $logger = new Logger($channel);
        $logger->pushHandler(new StreamHandler($this->config['run_log'] , Logger::DEBUG));

        $this->loggers[$channel] = $logger;

        return $logger;
    }

}

