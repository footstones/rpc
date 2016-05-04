<?php

namespace Footstones\RPC\Examples;

use Footstones\RPC\AbstractServerProcess;

use \swoole_process;

class Test1Process extends AbstractServerProcess
{
    public function loop($process)
    {
        swoole_timer_after(1000, [$this, 'timerLoop']);
    }

    public function timerLoop()
    {
        echo "test1 process loop.\n";
        swoole_timer_after(1000, [$this, 'timerLoop']);
    }
}