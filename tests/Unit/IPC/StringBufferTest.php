<?php

namespace SubProcess\Unit\IPC;

use PHPUnit\Framework\TestCase;

class StringBufferTest extends TestCase
{
    /** @test */
    public function whenAppenThenBufferGrows()
    {
        $buffer = new \SubProcess\IPC\StringBuffer();
        
        $buffer->append("Hello");
        $buffer->append(", ");
        $buffer->append("World!");
        
        $this->assertEquals("World!", $buffer->read(7));
        $this->assertEquals("Hello, World!", $buffer->read(0));
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
    public function whenRemoveThenBufferShrinks($offset, $length, $expectedString)
    {
        $buffer = new \SubProcess\IPC\StringBuffer();       
        $buffer->append("Hello, World");
        
        $buffer->remove($offset, $length);
        
        $this->assertEquals($expectedString, $buffer->read(0));
    }
}
