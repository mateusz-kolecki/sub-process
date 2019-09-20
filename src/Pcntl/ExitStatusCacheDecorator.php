<?php

namespace SubProcess\Pcntl;

use SubProcess\Guards\TypeGuard;
use SubProcess\Pcntl;

class ExitStatusCacheDecorator implements Pcntl
{
    /** @var Pcntl */
    private $pcntl;

    /** @varr ExitStatus[] */
    private $statuses = array();

    public function __construct(Pcntl $pcntl)
    {
        $this->pcntl = $pcntl;
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        $status = $this->pcntl->wait();
        $this->statuses[$status->pid()] = $status;
        return $status;
    }

    /**
     * {@inheritDoc}
     */
    public function waitPid($pid)
    {
        TypeGuard::assertInt($pid);

        if (isset($this->statuses[$pid])) {
            $status = $this->statuses[$pid];
            unset($this->statuses[$pid]);

            return $status;
        }

        return $this->pcntl->waitPid($pid);
    }

    /**
     * {@inheritDoc}
     */
    public function fork()
    {
        return $this->pcntl->fork();
    }

    /**
     * @param int $pid
     */
    public function removeStatus($pid)
    {
        TypeGuard::assertInt($pid);

        unset($this->statuses[$pid]);
    }
}
