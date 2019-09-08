<?php

namespace SubProcess\Unit\IPC\Channel;

use PHPUnit\Framework\TestCase;
use SubProcess\IPC\Channel\SerialiseChannel;
use SubProcess\IPC\Stream\ResourceStream;
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
        $fd = fopen("php://memory", "rw");
        $stream = new ResourceStream($fd);

        $channelA = new SerialiseChannel($stream);
        $channelA->send($sendData);

        fseek($fd, 0);

        $channelB = new SerialiseChannel($stream);
        $receivedData = $channelB->read();

        $this->assertEquals($sendData, $receivedData);
    }
}
