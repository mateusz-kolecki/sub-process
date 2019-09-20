<?php

namespace SubProcess;

use Exception;
use Iterator;
use LogicException;
use SubProcess\Guards\TypeGuard;
use SubProcess\IPC\Channel\SerialiseChannel;
use SubProcess\IPC\Channel;
use SubProcess\IPC\Stream\ResourceStream;
use SubProcess\Pcntl\RealPcntl;

class Process
{
    const STATE_NOT_RUNNING = 0;
    const STATE_PARENT = 1;
    const STATE_CHILD = 2;
    const STATE_EXITED = 3;

    /** @var callable */
    private $callback;

    /** int */
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
     */
    public function __construct($callback, Pcntl $pcntl = null)
    {
        TypeGuard::assertCallable($callback);

        $this->callback = $callback;
        $this->pcntl = $pcntl ? $pcntl : new RealPcntl();
    }

    /**
     * @param mixed $returnValue
     * @return int
     */
    private function exitCode($returnValue)
    {
        if ($returnValue === null) {
            return 0;
        }

        if (\is_int($returnValue)) {
            return $returnValue;
        }

        if (\is_numeric($returnValue)) {
            return (int) $returnValue;
        }

        if ($returnValue) {
            return 0;
        }

        return 1;
    }

    /**
     * @throws ForkError
     */
    public function start()
    {
        if (!in_array($this->state, array(self::STATE_NOT_RUNNING, self::STATE_EXITED))) {
            throw ForkError::whenAlreadyStarted();
        }

        list($parentChannel, $childChannel) = $this->createChannelPair();

        $pid = $this->pcntl->fork();

        if ($pid > 0) {
            $this->state = self::STATE_PARENT;

            $childChannel->close();
            $this->channel = $parentChannel;
            $this->exitStatus = null;
            $this->pid = $pid;

            return;
        }

        $this->state = self::STATE_CHILD;

        $parentChannel->close();
        $this->channel = $childChannel;
        $this->pid = \getmypid();

        try {
            $returnValue = $this->runCallback();
            $exitCode = $this->exitCode($returnValue);
        } catch (Exception $e) {
            $exitCode = $e->getCode() > 0 ? $e->getCode() : 1;
        }

        $this->channel->close();
        $this->state = self::STATE_NOT_RUNNING;
        exit($exitCode);
    }

    private function runCallback()
    {
        $result = \call_user_func($this->callback, $this);

        if (!($result instanceof Iterator)) {
            return $result;
        }

        foreach ($result as $key => $value) {
            $this->channel->send(array($key, $value));
        }

        return 0;
    }

    public function setName($name)
    {
        TypeGuard::assertString($name);

        \cli_set_process_title($name);
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
        if ($this->state === self::STATE_CHILD) {
            throw new LogicException("You cannot wait in child process for it self");
        }

        if ($this->state === self::STATE_NOT_RUNNING) {
            throw new LogicException("You cannot wait for not started process");
        }

        if ($this->state === self::STATE_PARENT) {
            $this->exitStatus = $this->pcntl->waitPid($this->pid);

            $this->state = self::STATE_EXITED;
            $this->pid = null;
            $this->channel()->close();
        }

        return $this->exitStatus;
    }

    /**
     * @return ExitStatus
     * @var ExitStatus
     */
    public function exitStatus()
    {
        if ($this->state !== self::STATE_EXITED) {
            throw new LogicException("Process did not exited");
        }

        return $this->exitStatus;
    }
}
