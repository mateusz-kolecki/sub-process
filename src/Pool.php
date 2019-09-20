<?php

namespace SubProcess;

use Countable;
use InvalidArgumentException;
use SubProcess\Guards\TypeGuard;
use SubProcess\Pcntl\ExitStatusCacheDecorator;
use SubProcess\Pcntl\RealPcntl;

class Pool extends EventEmitter implements Countable
{
    /** @var Process[] */
    private $processes = array();

    /** @var callable */
    private $callback;

    /** @var ExitStatusCacheDecorator */
    private $pcntl;

    /**
     * @param callable $callback
     * @param Pcntl $pcntl
     */
    public function __construct(
        $callback,
        Pcntl $pcntl
    ) {
        TypeGuard::assertCallable($callback);

        $this->callback = $callback;
        $this->pcntl = new ExitStatusCacheDecorator($pcntl);
    }

    /**
     * @param callable $callback
     * @return self
     */
    public static function create($callback)
    {
        return new self(
            $callback,
            new RealPcntl()
        );
    }

    /**
     * @param int $number
     * @throws ForkError
     */
    public function start($number)
    {
        TypeGuard::assertInt($number);

        if ($number <= 0) {
            throw new InvalidArgumentException("Requested workers number must be greater than zero");
        }

        for ($i = 0; $i < $number; $i++) {
            $worker = new Process(
                $this->callback,
                $this->pcntl
            );

            $worker->start();
            $this->processes[$worker->pid()] = $worker;
        }
    }

    /**
     * @return Process
     * @throws ForkError
     */
    public function wait()
    {
        do {
            $exitStatus = $this->pcntl->wait();
            $pid = $exitStatus->pid();

            $worker = isset($this->processes[$pid])
                ? $this->processes[$pid]
                : null;

            // when null then process was not managed by this pool
            if ($worker !== null) {
                unset($this->processes[$pid]);
                $worker->wait();
                $this->pcntl->removeStatus($pid);

                $this->emit('exit', $exitStatus, $worker);
            }

        } while ($worker === null);

        return $worker;
    }

    public function count()
    {
        return count($this->processes);
    }
}
