<?php

namespace Footstones\RPC;

use Footstones\RPC\Packager;
use Footstones\RPC\Consts;

class Server
{
    protected $service;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function handle($service, $data, $request)
    {
        $this->logger->debug('request: '. $data, $request);
        $response = [];

        try {
            $unpacked = Packager::unpack($data);
            $this->logger->debug('unpacked request', $unpacked);
        } catch(\Exception $e) {
            $response['s'] = Consts::ERR_PACKAGER;
            $response['e'] = [
                'message' => $e->getMessage(),
            ];
            $this->logger->error("unpack error: {$response['e']}", $request);
            goto end;
        }

        $transaction = $unpacked['header']['transactionId'];
        $method = $unpacked['body']['m'];
        $parameters = $unpacked['body']['p'];

        $this->logger->info("{$request['remote_addr']} {$request['request_method']} {$request['request_uri']} {$method}", $parameters);

        if (!is_callable([$service, $method])) {
            $response['s'] = Consts::ERR_REQUEST;
            $response['e'] = [
                'message' => sprintf("call to undefined api %s::%s", get_class($service), $method),
            ];

            $this->logger->warning('api undefined', $response);
            goto end;
        }

        try {
            ob_start();
            
            $response['r'] = call_user_func_array([$service, $method], $parameters);
            $response['s'] = Consts::ERR_OKEY;

            $this->logger->debug('api call result', $response['r']);

            $output = ob_get_contents();
            ob_end_clean();

            if (strlen($output) > 0) {
                $response['o'] = $output;
                $this->logger->debug('api call output: ', $output);
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
            $this->logger->warning('api call exception', $response);
        }

        end:
        $this->logger->debug('response', $response);
        $response = Packager::pack($response, isset($transaction) ? $transaction : null);

        return $response;

    }
}

