<?php

namespace Footstones\RPC;

use Footstones\RPC\Packager;
use Footstones\RPC\Consts;

class Server
{
    protected $service;

    public function __construct($service = null)
    {
        if ($service) {
            $this->service = $service;
        }
    }

    public function setService($service)
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
            ob_start();
            
            $response['r'] = call_user_func_array([$this->service, $method], $parameters);
            $response['s'] = Consts::ERR_OKEY;

            $output = ob_get_contents();
            ob_end_clean();

            if (strlen($output) > 0) {
                $response['o'] = $output;
            }
            
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

