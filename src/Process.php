<?php

namespace SubProcess;

use Exception;
use InvalidArgumentException;
use LogicException;
use SubProcess\Guards\TypeGuard;
use SubProcess\IPC\Channel;
use SubProcess\IPC\Channel\SerialiseChannel;
use SubProcess\IPC\Stream\ResourceStream;
use SubProcess\Pcntl\RealPcntl;

final class Process
{
    const STATE_NOT_RUNNING = 0;
    const STATE_RUNNING     = 1;
    const STATE_EXITED      = 3;

    /** @var callable */
    private $callback;

    /** int|null */
    private $pid = null;

    /** @var Channel */
    private $channel = null;

    /** @var int */
    private $state = self::STATE_NOT_RUNNING;

    /** @var ExitStatus|null */
    private $exitStatus = null;

    /** @var Pcntl */
    private $pcntl;

    /**
     * @param callable $callback
     * @param Pcntl|null $pcntl
     * @throws InvalidArgumentException
     */
    public function __construct($callback, Pcntl $pcntl = null)
    {
        TypeGuard::assertCallable($callback);

        $this->callback = $callback;
        $this->pcntl = $pcntl ? $pcntl : new RealPcntl();
    }

    /**
     * @return void
     * @throws ForkError
     */
    public function start()
    {
        if ($this->state === self::STATE_RUNNING) {
            throw ForkError::whenAlreadyStarted();
        }

        list($parentChannel, $childChannel) = $this->createChannelPair();

        $pid = $this->pcntl->fork();
        $this->exitStatus = null;

        if ($pid > 0) {
            $this->state = self::STATE_RUNNING;

            $childChannel->close();
            $this->channel = $parentChannel;
            $this->pid = $pid;

            return;
        }

        $parentChannel->close();

        try {
            \call_user_func($this->callback, new Child(
                \getmypid(),
                $childChannel
            ));
            $exitCode = 0;
        } catch (Exception $e) {
            $exitCode = $e->getCode() > 0 ? $e->getCode() : 1;
        }

        $childChannel->close();

        exit($exitCode);
    }

    /**
     * @return Channel[]
     * @throws ForkError
     */
    private function createChannelPair()
    {
        $socketPair = \stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            0
        );

        if ($socketPair === false) {
            throw ForkError::whenCannotOpenIpc();
        }

        return array(
            new SerialiseChannel(new ResourceStream($socketPair[0])),
            new SerialiseChannel(new ResourceStream($socketPair[1])),
        );
    }

    /**
     * @return Channel
     */
    public function channel()
    {
        return $this->channel;
    }

    /**
     * @return int|null
     */
    public function pid()
    {
        return $this->pid;
    }

    /**
     * @return ExitStatus
     * @throws ForkError
     * @throws LogicException
     */
    public function wait()
    {
        if ($this->state !== self::STATE_RUNNING) {
            throw new LogicException("You cannot wait for not started process");
        }

        $this->exitStatus = $this->pcntl->waitPid($this->pid);

        $this->state = self::STATE_EXITED;
        $this->pid = null;
        $this->channel->close();

        return $this->exitStatus;
    }

    /**
     * @return ExitStatus
     * @throws LogicException
     */
    public function exitStatus()
    {
        if ($this->state === self::STATE_NOT_RUNNING) {
            throw new LogicException("Process did not stated");
        }

        if ($this->state === self::STATE_RUNNING) {
            throw new LogicException("Process did not exited");
        }

        return $this->exitStatus;
    }
}
