<?php

namespace SubProcess;

use Iterator;
use Exception;

class Process
{
    /** @var callable */
    private $callback;

    /** int */
    private $pid = null;

    /** @var Channel */
    private $channel = null;

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
        list($parentSocket, $childSocket) = $this->socketPair();

        $pid = pcntl_fork();

        if ($pid === -1) {
            fclose($parentSocket);
            fclose($childSocket);
            throw ForkError::whenCannotFork();
        }

        if ($pid > 0) {
            // parent
            fclose($childSocket);
            $this->channel = new Channel($parentSocket);
            $this->pid = $pid;
        } else {
            // child
            fclose($parentSocket);
            $this->channel = new Channel($childSocket);
            $this->pid = getmypid();

            try {
                $returnValue = $this->call();
                $exitCode = $this->exitCode($returnValue);
            } catch (Exception $e) {
                $exitCode = $e->getCode() > 0 ? $e->getCode() : 1;
            }

            $this->channel->close();

            exit($exitCode);
        }
    }

    private function call()
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
}
