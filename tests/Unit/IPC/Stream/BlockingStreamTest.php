<?php

namespace SubProcess\Unit\IPC\Stream;

use PHPUnit\Framework\TestCase;
use SubProcess\IPC\Stream\BlockingStream;

class BlockingStreamTest extends TestCase
{
    /** @var resource */
    private $fd;

    /** @var BlockingStream */
    private $stream;


    public function setUp()
    {
        $this->fd = \fopen("php://memory", "rw");
        $this->stream = new BlockingStream($this->fd);
    }

    protected function tearDown()
    {
        $this->stream->close();
    }

    /** @test */
    public function when_wrote_data_then_read_should_return_that_data()
    {
        $this->stream->write("Hello, World!\n");
        $this->stream->write("Multiple writes");

        \fseek($this->fd, 0);

        $result = $this->stream->read(14 + 15);

        $this->assertEquals("Hello, World!\nMultiple writes", $result);
    }
}
