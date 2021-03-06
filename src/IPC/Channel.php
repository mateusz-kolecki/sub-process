<?php

namespace SubProcess\IPC;

interface Channel
{
    /**
     * @param mixed $data
     * @return void
     */
    public function send($data);

    /**
     * @return mixed
     */
    public function read();

    /**
     * @return Stream
     */
    public function stream();

    /**
     * @return void
     */
    public function close();

    /**
     * @return boolean
     */
    public function eof();
}
