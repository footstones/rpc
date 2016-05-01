<?php

namespace Footstones\RPC;

class ServerException extends \Exception
{
    protected $_type;

    public function __construct($error)
    {
        $this->message = $error['message'];
        $this->code = $error['code'];
        $this->file = $error['file'];
        $this->line = $error['line'];
        $this->_type = $error['_type'];
    }

    public function getType()
    {
        return $this->_type;
    }
}