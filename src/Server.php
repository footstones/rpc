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
        $response = [];

        try {
            $unpacked = Packager::unpack($data);
        } catch(\Exception $e) {
            $response['s'] = Consts::ERR_PACKAGER;
            $response['e'] = [
                'message' => $e->getMessage(),
            ];
            goto end;
        }

        $method = $unpacked['body']['m'];
        $parameters = $unpacked['body']['p'];

        if (!is_callable([$this->service, $method])) {
            $response['s'] = Consts::ERR_REQUEST;
            $response['e'] = [
                'message' => sprintf("call to undefined api %s::%s", get_class($this->service), $method),
            ];
            goto end;
        }

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

        end:
        return Packager::pack($response);
    }
}

