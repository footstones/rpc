<?php

namespace Footstones\RPC\Tests;

use Footstones\RPC\Server;
use Footstones\RPC\Packager;
use Footstones\RPC\Consts;
use Footstones\RPC\Tests\TestService;
use Footstones\RPC\Client;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    // public function testEchoName()
    // {
    //     $result = $this->getTestService()->echoName('li lei');

    //     var_dump($result);
    // }

    public function testEchoNameWithYar()
    {
        $result = $this->getYarTestService()->echoName('li lei');

        var_dump($result);
        exit();
    }
    
    protected function getTestService()
    {
        return new Client('http://127.0.0.1:10000/');
    }

    protected function getYarTestService()
    {
        $client = new \Yar_Client('http://127.0.0.1:10000/');
        return $client;
        return new Yar_Debug_Client('http://127.0.0.1:10000/');
    }

}

class Yar_Debug_Client {
    private $url;
    public function __construct($url) {
        $this->url = $url;
    }
    public function call($method, $arguments) {
        return Yar_Debug_Transports::exec($this->url, Yar_Debug_Protocol::Package($method, $arguments));
    }
    public function __call($method, $arguments) {
        return Yar_Debug_Transports::exec($this->url, Yar_Debug_Protocol::Package($method, $arguments));
    }
}


class Yar_Debug_Protocol {
    public static function Package($m, $params) {
        $struct = array(
            'i' =>  time(),
            'm' =>  $m,
            'p' =>  $params,
        );
        $body = str_pad('PHP', 8, chr(0)) . serialize($struct);
        $transaction = sprintf('%08x', mt_rand());
        $header = '';
        $header = $transaction;                     //transaction id
        $header .= sprintf('%04x', 0);              //protocl version
        $header .= '80DFEC60';                      //magic_num, default is: 0x80DFEC60
        $header .= sprintf('%08x', 0);              //reserved
        $header .= sprintf('%064x', 0);             //reqeust from who
        $header .= sprintf('%064x', 0);             //request token, used for authentication
        $header .= sprintf('%08x', strlen($body));  //request body len
        $data = '';
        for($i = 0; $i < strlen($header); $i = $i + 2)
            $data .= chr(hexdec('0x' . $header[$i] . $header[$i + 1]));
        $data .= $body;
        return array(
            'transaction'   =>  $transaction,
            'data'          =>  $data
        );
    }
}


class Yar_Debug_Transports {
    public static function exec($url, $data) {
        $urlinfo = parse_url($url);

        $port = isset($urlinfo["port"])? $urlinfo["port"] : 80;
        $path = $urlinfo['path'] . (!empty($urlinfo['query']) ? '?' . $urlinfo['query'] : '') . (!empty($urlinfo['fragment']) ? '#' . $urlinfo['fragment'] : '');
        $in = "POST {$path} HTTP/1.1\r\n";
        $in .= "Host: {$urlinfo['host']}\r\n";
        $in .= "Content-Type: application/octet-stream\r\n";
        $in .= "Connection: Close\r\n";
        $in .= "Hostname: {$urlinfo['host']}\r\n";
        $in .= "Content-Length: " . strlen($data['data']) . "\r\n\r\n";
        $address = gethostbyname($urlinfo['host']);
        $fp = fsockopen($address, $port, $err, $errstr);
        if (!$fp) {
            die ("cannot conncect to {$address} at port {$port} '{$errstr}'");
        }
        fwrite($fp, $in . $data['data'], strlen($in . $data['data']));
        $f_out = '';
        while ($out = fread($fp, 2048))
            $f_out .= $out;
        $tmp = explode("\r\n\r\n", $f_out);
        return array (
            'header'    =>  $tmp[0],
            'body'      =>  $tmp[1],
            'return'    =>  unserialize(substr($tmp[1], 82 + 8)),
        );
        fclose($fp);
    }
}
