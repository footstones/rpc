<?php

namespace Footstones\RPC\Tests;

use Footstones\RPC\Tests\TestServiceException;

class TestService
{
    public function methodHasNoParameters()
    {
        return 'methodHasNoParameters';
    }

    public function methodHasParameters($a)
    {
        return 'methodHasParameters.' . $a;
    }

    public function methodWithException()
    {
        throw new TestServiceException('this is a exception', 123);
    }
    
}