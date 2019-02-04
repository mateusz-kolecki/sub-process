<?php

namespace SubProcess\Unit\IPC\Stream;

use PHPUnit\Framework\TestCase;
use SubProcess\IPC\Stream\InMemoryStream;

class InMemoryStreamTest extends TestCase
{    
    /** @test */
    public function when_wrote_data_to_loop_then_read_should_return_that_data()
    {
        $stream = InMemoryStream::createLoop();
        
        $stream->write("Hello, World!\n");
        $stream->write("Multiple writes");
        
        $result = $stream->read(14 + 15);
        
        $this->assertEquals("Hello, World!\nMultiple writes", $result);
    }
    
    /** @test */
    public function when_write_and_read_multiple_times_then_total_should_match()
    {
        $stream = InMemoryStream::createLoop();
        
        $stream->write("Hello, ");
        $stream->write("Worl");
        $result = $stream->read(5);
        
        $stream->write("d!\n");
        $stream->write("Multiple ");
        $result .= $stream->read(5);
        
        $stream->write("writes");
        
        $result .= $stream->read(19);
        
        $this->assertEquals("Hello, World!\nMultiple writes", $result);
    }
}
