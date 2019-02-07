<?php

namespace SubProcess\PcntlWrapper;

use SubProcess\ExitStatus;
use SubProcess\ForkError;
use SubProcess\PcntlWrapper;

class SimpleWrapper implements PcntlWrapper
{

    public function fork()
    {
        $result = pcntl_fork();

        if ($result === -1) {
            throw ForkError::fromPcntlErrno(
                pcntl_get_last_error()
            );
        }

        return $result;
    }

    public function wait()
    {
        $status = null;
        $pid = pcntl_wait($status);

        if ($pid === -1) {
            throw ForkError::fromPcntlErrno(
                pcntl_get_last_error()
            );
        }

        return array($pid, ExitStatus::createFromPcntlStatus($status));
    }

    function waitPid($pid)
    {
        if (!is_int($pid) || $pid <=0) {
            throw new \Exception("Cannot wait on pid that is {$pid}, must be > 0");
        }
        if (pcntl_waitpid($pid, $status) === -1) {
            throw ForkError::fromPcntlErrno(
                pcntl_get_last_error()
            );
        }

        return ExitStatus::createFromPcntlStatus($status);
    }

}
