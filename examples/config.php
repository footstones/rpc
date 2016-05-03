<?php

return [
    'host' => '0.0.0.0',
    'port' => 10000,
    'daemonize' => 1,
    'pid_path' => '/tmp/plumber.pid',
    'log_path' => '/tmp/plumber.log',
    'output_path' => '/tmp/plumber.output.log',
    'bootstrap' => __DIR__ . '/bootstrap.php',
    'handler' => 'Footstones\\RPC\\Examples\\ExampleServerHandler'
];
