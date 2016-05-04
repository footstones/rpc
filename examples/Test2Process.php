<?php

namespace Footstones\RPC\Examples;

use Footstones\RPC\AbstractServerProcess;

use \swoole_process;

class Test2Process extends AbstractServerProcess
{
    public function loop($process)
    {
        swoole_timer_after(1000, [$this, 'timerLoop']);
    }

    public function timerLoop()
    {
        echo "test2 process loop.\n";
        swoole_timer_after(1000, [$this, 'timerLoop']);
    }
}