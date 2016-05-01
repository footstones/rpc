<?php

namespace Footstones\RPC;

use Footstones\RPC\Packager;

use Footstones\RPC\Consts;

class Server
{
    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function handle($data)
    {
        $unpacked = Packager::unpack($data);

        $method = $unpacked['body']['m'];
        $parameters = $unpacked['body']['p'];

        $response = [];

        try {
            $response['r'] = call_user_func_array([$this->service, $method], $parameters);
            $response['s'] = Consts::ERR_OKEY;
        } catch(\Exception $e) {
            $response['s'] = Consts::ERR_EXCEPTION;
            $response['e'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                '_type' => get_class($e),
            ];
        }

        return Packager::pack($response);
    }
}

