<?php

namespace Footstones\RPC;

use \swoole_process;

abstract class AbstractServerProcess
{
    protected $process;

    protected $name;

    protected $server;

    public function __construct($server, $name)
    {
        $this->server = $server;
        $this->name = $name;
        $this->process = new swoole_process([$this, 'main']);
    }

    public function main($process)
    {
        swoole_set_process_name(sprintf('%s: %s process [> #%s]', $this->server->getServerName(), $this->name, $this->server->getServerPid()));
        $this->loop($process);
    }

    abstract public function loop($process);

    public function getProcess()
    {
        return $this->process;
    }
}