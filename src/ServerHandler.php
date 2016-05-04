<?php

namespace Footstones\RPC;

interface ServerHandler
{
    public function onRequest($request, $response);
}