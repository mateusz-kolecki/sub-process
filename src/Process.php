<?php

namespace SubProcess;

use Iterator;
use Exception;
use LogicException;
use SubProcess\IPC\Channel\SerialiseChannel;
use SubProcess\IPC\Stream\BlockingStream;

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

    /**
     * @param callable $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param mixed $returnValue
     */
    private function exitCode($returnValue)
    {
        if ($returnValue === null) {
            return 0;
        }

        if (is_int($returnValue)) {
            return $returnValue;
        }

        if (is_numeric($returnValue)) {
            return (int) $returnValue;
        }

        if ($returnValue) {
            return 0;
        }

        return 1;
    }

    public function start()
    {
        if (!in_array($this->state, array(self::STATE_NOT_RUNNING, self::STATE_EXITED))) {
            throw ForkError::whenAlreadyStarted();
        }

        list($parentSocket, $childSocket) = $this->socketPair();

        $pid = pcntl_fork();

        if ($pid === -1) {
            fclose($parentSocket);
            fclose($childSocket);
            throw ForkError::fromPcntlErrno(pcntl_get_last_error());
        }

        if ($pid > 0) {
            $this->state = self::STATE_PARENT;
            $this->exitStatus = null;

            fclose($childSocket);
            $this->channel = new SerialiseChannel(new BlockingStream($parentSocket));
            $this->pid = $pid;
        } else {
            $this->state = self::STATE_CHILD;

            fclose($parentSocket);
            $this->channel = new SerialiseChannel(new BlockingStream($childSocket));
            $this->pid = getmypid();

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
    }

    private function runCallback()
    {
        $result = call_user_func($this->callback, $this);

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
        cli_set_process_title($name);
    }

    private function socketPair()
    {
        $socketPair = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            0
        );

        if ($socketPair === false) {
            throw ForkError::whenCannotOpenIpc();
        }

        return $socketPair;
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
            pcntl_waitpid($this->pid, $status);

            $this->state = self::STATE_EXITED;
            $this->pid = null;
            $this->channel()->close();

            $this->exitStatus = ExitStatus::createFromPcntlStatus($status);
        }

        return $this->exitStatus;
    }

    /**
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
