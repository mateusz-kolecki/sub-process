<?php

namespace SubProcess;

class ForkError extends \Exception
{
    public function whenAlreadyStarted()
    {
        return new self("Cannot start new procces because it is alredy running");
    }
    public static function fromPcntlErrno($errno)
    {
        return new self(pcntl_strerror($errno));
    }

    /** @return self */
    public static function whenCannotOpenIpc()
    {
        return new self("Cannot open IPC communication channel");
    }
}
