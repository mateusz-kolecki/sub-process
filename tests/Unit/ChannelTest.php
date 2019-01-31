<?php

namespace SubProcess\Unit;

use PHPUnit\Framework\TestCase;
use SubProcess\Channel;

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
        return [
            [ '' ],
            [ 'some simple text' ],
            [ 'ðπœę©śəðśðłð…ðąəðó→əśðł…śńəó«↓»' ],
            [ (object)[] ],
            [ (object)['foo' => 'bar'] ],
            [ true ],
            [ false ],
            [ null ],
            [ [] ],
            [ [1,2,3,4,5] ],
            [ $this->text(1024 * 1232) ]
        ];
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
