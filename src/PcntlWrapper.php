<?php

namespace SubProcess;

interface PcntlWrapper
{
    /**
     * @return int
     */
    public function fork();

    /**
     * @return array
     */
    public function wait();

    /**
     * @param int $pid
     * @return ExitStatus
     */
    public function waitPid($pid);
}
