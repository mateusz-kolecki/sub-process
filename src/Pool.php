<?php

namespace SubProcess;

use Countable;
use InvalidArgumentException;
use SubProcess\PcntlWrapper\PoolWrapper;
use SubProcess\PcntlWrapper\SimpleWrapper;

class Pool extends EventEmmiter implements Countable
{

    /** @var Process[] */
    private $processes = array();

    /** @var callable */
    private $callback;

    /** @var PoolWrapper */
    private $pcntl;

    /**
     * @param callable $callback
     * @throws InvalidArgumentException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            $type = \is_object($callback) ? \get_class($callback) : \gettype($callback);
            throw new InvalidArgumentException("Callback must be callable but {$type} given");
        }

        $this->callback = $callback;
        $this->pcntl = new PoolWrapper(new SimpleWrapper());
    }

    public function start($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->spawn(new Process($this->callback));
        }
    }

    public function spawn(Process $worker)
    {
        $worker->setPcntlWrapper($this->pcntl);
        $worker->start();

        $this->processes[$worker->pid()] = $worker;
    }

    private function getChildByPid($pid)
    {
        return isset($this->processes[$pid]) ? $this->processes[$pid] : null;
    }

    private function removeChildByPid($pid)
    {
        unset($this->processes[$pid]);
    }

    /**
     * @return Process
     */
    public function wait()
    {
        do {
            list($pid) = $this->pcntl->wait();

            $worker = $this->getChildByPid($pid);

            if ($worker) {
                $this->removeChildByPid($pid);
                $exitInfo = $worker->wait();
                $this->emit('exit', $exitInfo, $worker);
            }
        } while ($worker === null);

        return $worker;
    }

    public function count()
    {
        return count($this->processes);
    }

}
