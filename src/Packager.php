<?php

namespace Footstones\RPC;

use Footstones\RPC\UnpackException;

class Packager
{

    public static function pack($struct, $transaction = null)
    {
        $body = str_pad('PHP', 8, chr(0)) . serialize($struct);

        if (!$transaction) {
            $transaction = sprintf('%08x', mt_rand());
        }

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

    public static function unpack($data)
    {
        $header = static::_unpackHeader($data);
        $body = substr($data, 82);
        $protocol = trim(substr($body, 0, 8));

        if (strlen($body) != hexdec($header['bodyLength'])) {
            throw new UnpackException('body length error.');
        }

        $body = unserialize(substr($body, 8));
        if (empty($body)) {
            throw new UnpackException('unpack body error.');
        }

        return [
            'header' => $header,
            'protocol' => $protocol,
            'body' => $body,
        ];
    }

    private static function _unpackHeader($data)
    {
        $header = substr($data, 0, 82);
        $len = strlen($header);

        $raw = '';
        for($i=0; $i < $len; $i++) {
            $hex= dechex(ord($header[$i]));
            if (strlen($hex) == 1) {
                $hex = '0' . $hex;
            }
            $raw .= $hex;
        }

        $header = [
            'transactionId' => substr($raw, 0, 8),
            'protoclVersion' => substr($raw, 8, 4),
            'magicNum' => substr($raw, 12, 8),
            'reserved' => substr($raw, 20, 8),
            'from' => substr($raw, 28, 64),
            'token' => substr($raw, 92, 64),
            'bodyLength' => substr($raw, 156, 8),
        ];

        if (strtoupper($header['magicNum']) != '80DFEC60') {
            throw new UnpackException('malformed response header: ' . substr($data, 0, 200));
        }

        return $header;
    }
}