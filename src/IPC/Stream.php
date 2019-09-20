<?php

namespace SubProcess\IPC;

interface Stream
{
    /**
     * @param int $length
     * @return string
     */
    public function read($length);

    /**
     * @param string $data
     * @return void
     */
    public function write($data);

    /**
     * @return bool
     */
    public function eof();

    /**
     * @return void
     */
    public function close();

    /**
     * @return resource
     */
    public function resource();
}
