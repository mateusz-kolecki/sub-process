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

        Pool::create(array());
    }


    /** @test */
    public function when_child_exit_then_wait_return_exited_process()
    {
        $pool = Pool::create(function () {
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
        $pool = Pool::create(function () {
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
        $pool = Pool::create(function () {
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
        // given
        $pool = Pool::create(function () {
            // noop
        });

        // when
        $pool->start(5);

        // then

        $exitStatuses = array();
        while ($pool->count()) {
            $exitStatuses[] = $pool->wait()->exitStatus();
        }

        $this->assertCount(5, $exitStatuses);

        foreach ($exitStatuses as $info) {
            $this->assertTrue($info->normalExit());
            $this->assertEquals(0, $info->code());
        }
    }
}
