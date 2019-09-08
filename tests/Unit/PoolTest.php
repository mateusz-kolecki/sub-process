<?php

namespace SubProcess\Unit;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use SubProcess\Pool;
use PHPUnit\Framework\TestCase;
use SubProcess\Process;
use SubProcess\ExitStatus;

class PoolTest extends TestCase
{
    /** @test */
    public function when_created_with_not_callable_then_throws_error()
    {
        $this->setExpectedException('InvalidArgumentException');

        new Pool(array());
    }


    /** @test */
    public function when_child_exit_then_wait_return_exited_process()
    {
        $pool = new Pool(function () {
            // noop
        });

        $pool->start(1);
        $process = $pool->wait();

        $this->assertInstanceOf('\\SubProcess\\Process', $process);
        $this->assertEquals(0, $process->exitStatus()->code());
    }

    /** @test */
    public function when_child_exit_then_emit_exit_event()
    {
        $pool = new Pool(function () {
            // noop
        });

        $status = null;
        $child = null;

        $pool->on('exit', function (ExitStatus $_status, Process $_child) use (&$status, &$child) {
            $status = $_status;
            $child = $_child;
        });

        $pool->start(1);
        $process = $pool->wait();

        $this->assertSame($process, $child);
        $this->assertEquals($process->exitStatus(), $status);
    }

    /** @test */
    public function when_child_exit_then_pool_count_decrease()
    {
        $pool = new Pool(function () {
            // noop
        });

        $pool->start(2);
        $this->assertCount(2, $pool);

        $pool->wait();
        $this->assertCount(1, $pool);

        $pool->wait();
        $this->assertCount(0, $pool);
    }

    /** @test */
    public function when_callback_do_not_fail_then_all_workers_exit_successfully()
    {
        $pool = new Pool(function () {
            // noop
        });

        $exitStatusess = array();
        $pool->on('exit', function (ExitStatus $status) use (&$exitStatusess) {
            $exitStatusess[] = $status;
        });

        $pool->start(5);
        while ($pool->count()) {
            $pool->wait();
        }

        $this->assertCount(5, $exitStatusess);

        $expectedInfo = new ExitStatus(true, 0, false, null);
        foreach ($exitStatusess as $info) {
            $this->assertEquals($expectedInfo, $info);
        }
    }
}
