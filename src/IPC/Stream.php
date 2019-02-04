<?php

namespace SubProcess\IPC;

interface Stream
{
    public function read($length);
    public function write($data);
    public function eof();
    public function close();
}
