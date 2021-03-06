<?php

namespace SubProcess\Pcntl;

use SubProcess\ExitStatus;
use SubProcess\ForkError;
use SubProcess\Pcntl;

class RealPcntl implements Pcntl
{

    public function fork()
    {
        $result = \pcntl_fork();

        if ($result === -1) {
            throw ForkError::fromPcntlErrno(
                \pcntl_get_last_error()
            );
        }

        return $result;
    }

    public function wait()
    {
        $status = null;
        $pid = \pcntl_wait($status);

        if ($pid === -1) {
            throw ForkError::fromPcntlErrno(
                \pcntl_get_last_error()
            );
        }

        return ExitStatus::createFromPcntlStatus($pid, $status);
    }

    function waitPid($pid)
    {
        $status = null;

        if (\pcntl_waitpid($pid, $status) === -1) {
            throw ForkError::fromPcntlErrno(
                \pcntl_get_last_error()
            );
        }

        return ExitStatus::createFromPcntlStatus($pid, $status);
    }

}
