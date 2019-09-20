<?php

namespace SubProcess;

use SubProcess\Guards\TypeGuard;
use SubProcess\IPC\Channel;

final class Child
{
    /** @var int */
    private $pid;
    /** @var Channel */
    private $channel;

    /**
     * @param int $pid
     * @param Channel $channel
     */
    public function __construct(
        $pid,
        Channel $channel
    ) {
        TypeGuard::assertInt($pid);

        $this->pid = $pid;
        $this->channel = $channel;
    }

    /**
     * @return int
     */
    public function pid()
    {
        return $this->pid;
    }

    /**
     * @return Channel
     */
    public function channel()
    {
        return $this->channel;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setTitle($name)
    {
        TypeGuard::assertString($name);

        \cli_set_process_title($name);
    }
}
