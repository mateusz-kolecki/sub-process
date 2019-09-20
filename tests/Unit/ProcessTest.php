<?php

namespace SubProcess\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SubProcess\Child;
use SubProcess\Process;

class ProcessTest extends TestCase
{
    /** @test */
    public function when_callback_do_not_throw_then_exit_with_zero()
    {
        $process = new Process(function () {
            return null;
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals(0, $info->code());
        $this->assertTrue($info->normalExit());
    }

    /** @test */
    public function when_callback_throws_with_non_default_code_then_child_do_normal_exit_with_that_code()
    {
        $process = new Process(function () {
            throw new \Exception("", 11);
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals(11, $info->code());
        $this->assertTrue($info->normalExit());
    }

    /** @test */
    public function when_callback_throws_zero_code_then_child_do_normal_exit_with_error_code_one()
    {
        $process = new Process(function () {
            throw new \Exception();
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals(1, $info->code());
        $this->assertTrue($info->normalExit());
    }

    /** @test */
    public function when_child_send_then_parent_can_read()
    {
        $process = new Process(function (Child $child) {
            $child->channel()->send('Hello, World');
        });

        $process->start();
        $message = $process->channel()->read();
        $process->wait();

        $this->assertEquals('Hello, World', $message);
    }

    /** @test */
    public function when_process_start_then_can_get_pid()
    {
        $process = new Process(function (Child $child) {
            $child->channel()->send($child->pid());
        });

        $process->start();
        $pid = $process->pid();
        $message = $process->channel()->read();

        $process->wait();

        $this->assertInternalType('integer', $pid);
        $this->assertSame($message, $pid);
    }
}
