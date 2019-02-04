<?php

namespace SubProcess\Unit\IPC\Channel;

use PHPUnit\Framework\TestCase;
use SubProcess\IPC\Channel\SerialiseChannel;
use SubProcess\IPC\Stream\InMemoryStream;
use SubProcess\IPC\StringBuffer;
use SubProcess\Unit\Assets\PrivatePropsObject;

class SerialiseChannelTest extends TestCase
{
    private function text($length)
    {
        $chars = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
        $charsCount = strlen($chars);

        $buff = '';
        $i = 0;

        while (\strlen($buff) < $length) {
            $i = ($i + 1) % $charsCount;
            $buff .= $chars[$i];
        }

        return $buff;
    }

    public function messageDataProvider()
    {
        return array(
            array( '' ),
            array( 'some simple text' ),
            array( 'ðπœę©śəðśðłð…ðąəðó→əśðł…śńəó«↓»' ),
            array( (object)array() ),
            array( (object)array('foo' => 'bar') ),
            array( true ),
            array( false ),
            array( null ),
            array( array() ),
            array( array(1, 2, 3, 4, 5) ),
            array( new PrivatePropsObject("John", 21) ),
            array( $this->text(1024 * 1232) )
        );
    }

    /**
     * @test
     * @dataProvider messageDataProvider
     */
    public function when_sending_data_over_loop_stream_then_should_receive_equal_data($sendData)
    {
        $stream = InMemoryStream::createLoop();
        
        $channelA = new SerialiseChannel($stream);
        $channelA->send($sendData);

        $channelB = new SerialiseChannel($stream);
        $receivedData = $channelB->read();

        $this->assertEquals($sendData, $receivedData);
    }
    
    /**
     * @test
     * @dataProvider messageDataProvider
     */
    public function when_sending_data_over_stream_pair_then_other_end_should_receive_equal_data($sendData)
    {
        $redInbuffer = new StringBuffer();
        $redOutbuffer = new StringBuffer();
        $redStream = new InMemoryStream($redInbuffer, $redOutbuffer);
        $redChannel = new SerialiseChannel($redStream);
        
        $greenInBuffer = $redOutbuffer;
        $greenOutBuffer = $redInbuffer;
        $greenStream = new InMemoryStream($greenInBuffer, $greenOutBuffer);
        $greenChannel = new SerialiseChannel($greenStream);
        
        
        $redChannel->send($sendData);
        $greenChannel->send($sendData);
        
        $redReceivedData = $redChannel->read();
        $greenReceivedData = $greenChannel->read();

        $this->assertEquals($sendData, $redReceivedData);
        $this->assertEquals($sendData, $greenReceivedData);
    }
}
