<?php

namespace SubProcess\Unit\IPC;

use PHPUnit\Framework\TestCase;

class StringBufferTest extends TestCase
{
    /** @test */
    public function when_appen_then_buffer_collect_data()
    {
        $buffer = new \SubProcess\IPC\StringBuffer();
        
        $buffer->append("Hello");
        $buffer->append(", ");
        $buffer->append("World!");
        
        $this->assertEquals("World!", $buffer->read(7));
        $this->assertEquals("Hello, World!", $buffer->read(0));
    }
    
    /** @test */
    public function when_appen_then_buffer_size_grows()
    {
        $buffer = new \SubProcess\IPC\StringBuffer();
        
        $buffer->append("Hello");
        
        $this->assertEquals(5, $buffer->size());
        
        $buffer->append(", ");
        $buffer->append("World!");
        
        $this->assertEquals(13, $buffer->size());
    }
    
    public function bufferRemoveDataProvider()
    {
        return array(
            array(0, 7, "World"),
            array(5, 2, "HelloWorld"),
            array(5, 7, "Hello"),
        );
    }
    
    /**
     * @test
     * @dataProvider bufferRemoveDataProvider
     */
    public function when_remove_then_buffer_data_shrinks($offset, $length, $expectedString)
    {
        $buffer = new \SubProcess\IPC\StringBuffer();       
        $buffer->append("Hello, World");
        
        $buffer->remove($offset, $length);
        
        $this->assertEquals($expectedString, $buffer->read(0));
    }
    
    /**
     * @test
     * @dataProvider bufferRemoveDataProvider
     */
    public function when_remove_then_buffer_size_shrinks($offset, $length, $expectedString)
    {
        $buffer = new \SubProcess\IPC\StringBuffer();       
        $buffer->append("Hello, World");
        
        $buffer->remove($offset, $length);
        
        $this->assertEquals(strlen($expectedString), $buffer->size());
    }
}
