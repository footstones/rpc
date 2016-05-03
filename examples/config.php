<?php

return [
    'host' => '0.0.0.0',
    'port' => 10000,
    'daemonize' => 1,
    'worker_num' => 4,
    'server_name' => 'example server',
    'pid_file' => '/tmp/example_server.pid',
    'run_log' => '/tmp/example_server.log',
    'error_log' => '/tmp/example_server.error.log',
    'bootstrap' => __DIR__ . '/bootstrap.php',
    'handler' => 'Footstones\\RPC\\Examples\\ExampleServerHandler'
];
