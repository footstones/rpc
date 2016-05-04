<?php

namespace Footstones\RPC;

use \swoole_process;

abstract class AbstractServerProcess
{
    protected $process;

    public function __construct($server)
    {
        $this->server = $server;
        $this->process = new swoole_process([$this, 'loop']);
    }

    abstract public function loop($process);

    public function getProcess()
    {
        return $this->process;
    }
}