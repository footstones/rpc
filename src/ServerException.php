<?php

namespace Footstones\RPC;

class ServerException extends \Exception
{
    protected $_type = null;

    public function __construct($error)
    {
        foreach (['message', 'code', 'file', 'line', '_type'] as $key) {
            if (isset($error[$key])) {
                $this->{$key} = $error[$key];
            }
        }
    }

    public function getType()
    {
        return $this->_type;
    }
}