<?php

namespace SubProcess\PcntlWrapper;

use SubProcess\PcntlWrapper;

class DebugWrapper implements PcntlWrapper
{
    /** @var PcntlWrapper */
    private $wrapper;

    public function __construct(PcntlWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    private function debug($method, $action)
    {
        $className = get_class($this->wrapper);
        echo "{$className}::{$method}() -- {$action}\n";
    }

    public function wait()
    {
        $this->debug(__FUNCTION__, 'enter');
        $result = $this->wrapper->wait();
        $this->debug(__FUNCTION__, 'leave');
        return $result;
    }

    public function waitPid($pid)
    {
        $this->debug(__FUNCTION__, 'enter');
        $result = $this->wrapper->waitPid($pid);
        $this->debug(__FUNCTION__, 'leave');
        return $result;
    }

    public function fork()
    {
        $this->debug(__FUNCTION__, 'enter');
        $result = $this->wrapper->fork();
        $this->debug(__FUNCTION__, 'leave');
        return $result;
    }

}
