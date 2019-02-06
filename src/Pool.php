<?php

namespace SubProcess;

use Countable;
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

    public function __construct($callback)
    {
        $this->callback = $callback;
        $this->pcntl = new PoolWrapper(new SimpleWrapper());
    }

    public function start($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $process = new Process($this->callback);
            $process->setPcntlWrapper($this->pcntl);

            $this->spawn($process);
        }
    }

    public function spawn(Process $worker)
    {
        $worker->start();
        $this->processes[$worker->pid()] = $worker;
    }

    /**
     * @return Process
     */
    public function wait()
    {
        do {
            list($pid) = $this->pcntl->wait();

            $worker = isset($this->processes[$pid])
                ? $this->processes[$pid]
                : null;

            if ($worker) {
                unset($this->processes[$pid]);

                $exitInfo = $worker->wait();
                $this->emit('exit', $exitInfo, $worker);
            }

            $this->pcntl->removePidStatus($pid);
        } while ($worker === null);

        return $worker;
    }

    public function count()
    {
        return count($this->processes);
    }
}
