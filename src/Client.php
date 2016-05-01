<?php

namespace Footstones\RPC;

use Footstones\RPC\Packager;
use Footstones\RPC\Consts;

class Client
{
    protected $_uri;

    public function __construct($uri)
    {
        $this->_uri = $uri;
    }

    public function call($method, $parameters = [])
    {
        $data =  Packager::pack(['m' => $method, 'p' => $parameters]);
        $response = $this->_request($this->_uri, $data['data']);

        // var_dump($response);exit();

        switch ($response['body']['s']) {
            case Consts::ERR_OKEY:
                return $response['body']['r'];
            case Consts::ERR_EXCEPTION:
                throw new ServerException($response['body']['e']);
            default:
                # code...
                break;
        }


    }

    protected function _request($uri, $data)
    {
        $headers[] = 'Content-type: application/octet-stream';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'Footstones RPC Client 1.0');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,  $data);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $uri);

        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlinfo['header_size']);
        $body   = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        $context = array(
            'CURLINFO' => $curlinfo,
            'HEADER'   => $header,
            'BODY'     => $body
        );

        if (empty($curlinfo['namelookup_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] NAME_LOOK_UP_TIMEOUT", $context);
        }

        if (empty($curlinfo['connect_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] API_CONNECT_TIMEOUT", $context);
            throw new CloudAPIIOException("Connect api server timeout (url: {$url}).");
        }

        if (empty($curlinfo['starttransfer_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] API_TIMEOUT", $context);
            throw new CloudAPIIOException("Request api server timeout (url:{$url}).");
        }

        if ($curlinfo['http_code'] >= 500) {
            $this->logger && $this->logger->error("[{$requestId}] API_RESOPNSE_ERROR", $context);
            throw new CloudAPIIOException("Api server internal error (url:{$url}).");
        }

        return Packager::unpack($body);

    }

}