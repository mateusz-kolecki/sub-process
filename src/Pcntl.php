<?php

namespace SubProcess;

interface Pcntl
{
    /**
     * @return int
     */
    public function fork();

    /**
     * @return ExitStatus
     */
    public function wait();

    /**
     * @param int $pid
     * @return ExitStatus
     */
    public function waitPid($pid);
}
