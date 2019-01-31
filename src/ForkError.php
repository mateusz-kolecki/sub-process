<?php

namespace SubProcess;

class ForkError extends \Exception
{
    /** @return self */
    public static function whenCannotOpenIpc()
    {
        return new self("Cannot open IPC communication channel");
    }

    /** @return self */
    public static function whenCannotFork()
    {
        return new self("Cannot create child process");
    }
}
