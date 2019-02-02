<?php

namespace SubProcess\Unit;

use PHPUnit\Framework\TestCase;
use SubProcess\Channel;

use SubProcess\Unit\Assets\PrivatePropsObject;

class ChannelTest extends TestCase
{
    /** @var string */
    private $tmpFileName;

    public function setUp()
    {
        $this->tmpFileName = tempnam(__DIR__, 'tmp_channel');
    }

    public function tearDown()
    {
        unlink($this->tmpFileName);
    }

    private function text($length)
    {
        $chars = 'QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
        $charsCount = strlen($chars);

        $buff = '';
        $i = 0;

        while (strlen($buff) < $length) {
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
    public function sendingDataShouldWork($sendData)
    {
        $channelA = new Channel(fopen($this->tmpFileName, 'w'));
        $channelA->send($sendData);

        $channelB = new Channel(fopen($this->tmpFileName, 'r'));
        $receivedData = $channelB->read();

        $this->assertEquals($sendData, $receivedData);
    }
}
