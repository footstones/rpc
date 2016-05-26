<?php

return [
    'host' => '0.0.0.0',
    'port' => 10000,
    'daemonize' => 0,
    'worker_num' => 4,
    'server_name' => 'example server',
    'pid_file' => '/tmp/example_server.pid',
    'run_log' => '/tmp/example_server.log',
    'error_log' => '/tmp/example_server.error.log',
    'bootstrap' => __DIR__ . '/bootstrap.php',
    'handler' => 'Footstones\\RPC\\Examples\\ExampleServerHandler',
    'processes' => [
        'test1' => 'Footstones\\RPC\\Examples\\Test1Process',
        'test2' => 'Footstones\\RPC\\Examples\\Test2Process',
    ]
];
