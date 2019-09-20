<?php

namespace SubProcess;

use SubProcess\Guards\TypeGuard;

class ExitStatus
{
    /** @var int */
    private $pid;
    /** @var bool */
    private $normalExit;
    /** @var int|null */
    private $code;
    /** @var bool */
    private $signaledExit;
    /** @var int|null */
    private $termSignal;

    /**
     * @param int $pid
     * @param bool $normalExit
     * @param int|null $code
     * @param bool $signaledExit
     * @param int|null $termSignal
     */
    private function __construct(
        $pid,
        $normalExit,
        $code,
        $signaledExit,
        $termSignal
    ) {
        $this->pid = $pid;
        $this->normalExit = $normalExit;
        $this->code = $code;
        $this->signaledExit = $signaledExit;
        $this->termSignal = $termSignal;
    }

    /**
     * @param int $pid
     * @param int $status
     * @return ExitStatus
     */
    public static function createFromPcntlStatus($pid, $status)
    {
        TypeGuard::assertInt($pid);
        TypeGuard::assertInt($status);

        $normalExit = \pcntl_wifexited($status);
        $code = $normalExit ? \pcntl_wexitstatus($status) : null;
        $signaledExit = \pcntl_wifsignaled($status);
        $termSignal = $signaledExit ? \pcntl_wtermsig($status) : null;

        return new self(
            $pid,
            $normalExit,
            $code,
            $signaledExit,
            $termSignal
        );
    }

    /**
     * @return int
     */
    public function pid()
    {
        return $this->pid;
    }

    /**
     * @return bool
     */
    public function normalExit()
    {
        return $this->normalExit;
    }

    /**
     * @return int|null
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function signaledExit()
    {
        return $this->signaledExit;
    }

    /**
     * @return int|null
     */
    public function termSignal()
    {
        return $this->termSignal;
    }
}
