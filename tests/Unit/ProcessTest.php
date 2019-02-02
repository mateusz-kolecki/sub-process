<?php

namespace SubProcess\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SubProcess\Channel;
use SubProcess\Process;

class ProcessTest extends TestCase
{
    /** @test */
    public function when_callback_return_null_then_child_exit_success()
    {
        $process = new Process(function () {
            // noop
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals(0, $info->code());
        $this->assertTrue($info->normalExit());
    }

    /** @test */
    public function when_callback_return_int_number_then_child_exit_with_that_num()
    {
        $process = new Process(function () {
            return 3;
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals(3, $info->code());
        $this->assertTrue($info->normalExit());
    }

    public function numericDataProvider()
    {
        return array(
            array(0, 0),
            array("0", 0),
            array("0.0", 0),
            array("1.0", 1),
            array("1.1", 1),
            array("1e0", 1),
            array(0.0, 0),
            array(1.0, 1),
            array(1.1, 1),
            array(1e0, 1),
        );
    }

    /**
     * @test
     * @dataProvider numericDataProvider
     */
    public function when_callback_return_numeric_then_child_exit_with_that_number_as_int($numeric, $expectedCode)
    {
        $process = new Process(function () use ($numeric) {
            return $numeric;
        });

        $process->start();
        $info = $process->wait();

        $this->assertEquals($expectedCode, $info->code());
        $this->assertTrue($info->normalExit());
    }

    /** @test */
    public function when_callback_return_something_else_then_exit_with_zero()
    {
        $process = new Process(function () {
            return "hello world";
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
    public function when_callback_throws_then_child_do_normal_exit_with_error_code()
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
        $process = new Process(function (Process $child) {
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
        $process = new Process(function (Process $child) {
            $child->channel()->send($child->pid());
        });

        $process->start();
        $pid = $process->pid();
        $message = $process->channel()->read();

        $process->wait();

        $this->assertInternalType('integer', $pid);
        $this->assertSame($message, $pid);
    }

    /** @test */
    public function when_iterator_is_returned_then_all_element_are_send_over_channel()
    {
        $process = new Process(function () {
            // In PHP >= 5.5.0 you can use Generator which is also Iterator
            /*
            yield 'Hello From Child';

            for ($count = 0; $count < 3; $count++) {
                yield $count;
            }
            */
            return new ArrayIterator(array('Hello From Child', 0, 1, 2));
        });

        $process->start();

        $messages = array();
        while ($process->channel()->eof()) {
            list(, $messages[]) = $process->channel()->read();
        }
        $process->wait();

        $this->assertEquals(array("Hello From Child", 0, 1, 2), $messages);
    }
}
