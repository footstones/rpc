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

    public function __call($method, $parameters = [])
    {
        $data =  Packager::pack(['m' => $method, 'p' => $parameters]);
        $response = $this->_request($this->_uri, $data['data']);

        if (isset($response['body']['o'])) {
            echo $response['body']['o'];
        }

        switch ($response['body']['s']) {
            case Consts::ERR_OKEY:
                return $response['body']['r'];
            case Consts::ERR_PACKAGER:
                throw new ClientPackagerException($response['body']['e']);
            case Consts::ERR_PROTOCOL:
                throw new ClientPackagerException($response['body']['e']);
            case Consts::ERR_TRANSPORT:
                throw new ClientTransportException($response['body']['e']);
            case Consts::ERR_REQUEST:
            case Consts::ERR_EXCEPTION:
                throw new ServerException($response['body']['e']);
            default:
                throw new ClientException($response['body']['e']);
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
            throw new ClientTransportException("server address look up timeout.");
        }

        if (empty($curlinfo['connect_time'])) {
            throw new ClientTransportException("server connect timeout.");
        }

        if (empty($curlinfo['starttransfer_time'])) {
            throw new ClientTransportException("server request timeout.");
        }

        if ($curlinfo['http_code'] != 200) {
            throw new ClientTransportException("erver responsed non-200 code {$curlinfo['http_code']}");
        }

        return Packager::unpack($body);

    }

}