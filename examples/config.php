<?php

return [
    'host' => '0.0.0.0',
    'port' => 10000,
    'daemonize' => 0,
    'worker_num' => 4,
    'server_name' => 'example server',
    'pid_file' => '/tmp/example_server.pid',
    'log_file' => '/tmp/example_server.log',
    'output_file' => '/tmp/example_server.output.log',
    'bootstrap' => __DIR__ . '/bootstrap.php',
    'handler' => 'Footstones\\RPC\\Examples\\ExampleServerHandler'
];
