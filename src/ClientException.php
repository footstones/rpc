<?php

namespace Footstones\RPC;

class ClientException extends \Exception
{
    public function __construct($error)
    {
        foreach (['message', 'code', 'file', 'line'] as $key) {
            if (isset($error[$key])) {
                $this->{$key} = $error[$key];
            }
        }
    }

}