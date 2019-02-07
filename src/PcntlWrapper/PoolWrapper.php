<?php

namespace SubProcess\PcntlWrapper;

use SubProcess\ExitStatus;
use SubProcess\PcntlWrapper;

class PoolWrapper implements PcntlWrapper
{

    /** @var PcntlWrapper */
    private $wrapper;

    /** @varr ExitStatus[] */
    private $pidStatuses = array();

    public function __construct(PcntlWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function setPidStatus($pid, ExitStatus $status)
    {
        $this->pidStatuses[$pid] = $status;
    }

    public function removePidStatus($pid)
    {
        unset($this->pidStatuses[$pid]);
    }

    public function wait()
    {
        list($pid, $status) = $this->wrapper->wait();
        $this->setPidStatus($pid, $status);

        return array($pid, $status);
    }

    public function waitPid($pid)
    {
        if (isset($this->pidStatuses[$pid])) {
            $status = $this->pidStatuses[$pid];
            $this->removePidStatus($pid);
            
            return $status;
        }

        return $this->wrapper->waitPid($pid);
    }

    public function fork()
    {
        return $this->wrapper->fork();
    }

}
