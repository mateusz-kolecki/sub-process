<?php

namespace SubProcess\IPC\Channel;

use SubProcess\IPC\Stream;
use SubProcess\IPC\Channel;

class SerialiseChannel implements Channel
{
    /** @var Stream */
    private $stream;

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function send($data)
    {
        if ($this->stream === null) {
            throw new \Exception("Trying to send over closed channel");
        }

        $string = \serialize($data);

        // first send unsigned long with lenght of serialized data
        $this->stream->write(\pack("L", \strlen($string)));
        // then send data
        $this->stream->write($string);
    }

    public function read()
    {
        if ($this->stream === null) {
            throw new \Exception("Trying to read from closed channel");
        }

        // first read unsigned long as lenght of serialized data
        $length = \unpack("Llong", $this->stream->read(4));

        // then read serialized data with that length
        return \unserialize(
            $this->stream->read($length['long'])
        );
    }

    function stream()
    {
        return $this->stream;
    }

    public function close()
    {
        $this->stream->close();
    }

    public function eof()
    {
        return $this->stream->eof();
    }
}
