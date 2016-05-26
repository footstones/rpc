<?php

namespace Footstones\RPC;

use \swoole_process;

abstract class AbstractServerProcess
{
    protected $process;

    protected $serverName;

    protected $processName;

    public function __construct($server, $serverName, $processName)
    {
        $this->server = $server;
        $this->serverName = $serverName;
        $this->processName = $processName;
        $this->process = new swoole_process([$this, 'main']);
    }

    public function main($process)
    {
        swoole_set_process_name(sprintf('%s: %s process [> #%s]', $this->serverName, $this->processName, $this->server->master_pid));
        $this->loop($process);
    }

    abstract public function loop($process);

    public function getProcess()
    {
        return $this->process;
    }
}