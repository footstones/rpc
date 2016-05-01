<?php

namespace Footstones\RPC;

class Packager
{

    public static function pack($struct)
    {
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

    public static function unpack($data)
    {
        $header = static::_unpackHeader(substr($data, 0, 82));
        $protocol = trim(substr($data, 82, 8));
        $body = unserialize(substr($data, 90, hexdec($header['bodyLength'])));

        return [
            'header' => $header,
            'protocol' => $protocol,
            'body' => $body,
        ];
    }

    private static function _unpackHeader($header)
    {
        $raw = '';
        for($i=0; $i < strlen($header); $i++) {
            $hex= dechex(ord($header[$i]));
            if (strlen($hex) == 1) {
                $hex = '0' . $hex;
            }
            $raw .= $hex;
        }

        return [
            'transactionId' => substr($raw, 0, 8),
            'protoclVersion' => substr($raw, 8, 4),
            'magicNum' => substr($raw, 12, 8),
            'reserved' => substr($raw, 20, 8),
            'from' => substr($raw, 28, 64),
            'token' => substr($raw, 92, 64),
            'bodyLength' => substr($raw, 156, 8),
        ];
    }
}