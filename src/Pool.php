<?php

namespace SubProcess;

use Countable;

class Pool extends EventEmmiter implements Countable
{
    private $processes = [];
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function start($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $this->spawn(new Process(
                $this->callback
            ));
        }
    }

    public function spawn(Process $worker)
    {
        $worker->start();
        $this->processes[$worker->pid()] = $worker;
    }

    public function wait()
    {
        do {
            $pid = pcntl_wait($status);

            if ($pid === -1) {
                throw new \Exception("Error on waiting for child proccess");
            }

            $worker = isset($this->processes[$pid])
                ? $this->processes[$pid]
                : null;

            if ($worker) {
                unset($this->processes[$pid]);

                $exitInfo = ExitStatus::createFromPcntlStatus($status);
                $this->emit('exit', $worker, $exitInfo);
            }
        } while ($worker === null);

        return $worker;
    }

    public function count()
    {
        return count($this->processes);
    }
}
