<?php

namespace SubProcess;

class ExitStatus
{
    private $normalExit;
    private $code;
    private $signaledExit;
    private $termSignal;

    public function __construct($normalExit, $code, $signaledExit, $termSignal)
    {
        $this->normalExit = $normalExit;
        $this->code = $code;
        $this->signaledExit = $signaledExit;
        $this->termSignal = $termSignal;
    }

    public static function createFromPcntlStatus($status)
    {
        $normalExit = \pcntl_wifexited($status);
        $code = $normalExit ? \pcntl_wexitstatus($status) : null;
        $signaledExit = \pcntl_wifsignaled($status);
        $termSignal = $signaledExit ? \pcntl_wtermsig($status) : null;

        return new self(
            $normalExit,
            $code,
            $signaledExit,
            $termSignal
        );
    }

    public function normalExit()
    {
        return $this->normalExit;
    }

    public function code()
    {
        return $this->code;
    }

    public function signaledExit()
    {
        return $this->signaledExit;
    }

    public function termSignal()
    {
        return $this->termSignal;
    }
}
