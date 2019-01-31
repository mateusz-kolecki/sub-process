<?php

namespace SubProcess\Unit;

use SubProcess\Pool;
use PHPUnit\Framework\TestCase;
use SubProcess\Process;
use SubProcess\ExitStatus;

class PoolTest extends TestCase
{
    /** @test */
    public function foo()
    {
        $pool = new Pool(function (Process $worker) {
        });

        /** @var ExitStatus */
        $status = null;
        $pool->on('exit', function (Process $worker, ExitStatus $info) use ($pool, &$status) {
            $status = $info;
        });

        $pool->start(1);
        $pool->wait();

        $this->assertEquals(0, $status->code());
    }
}
