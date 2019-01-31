<?php

namespace SubProcess;

class Channel
{
    /** @var resource */
    private $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function send($data)
    {
        if ($this->stream === null) {
            throw new \Exception("Trying to send over closed channel");
        }

        $string = serialize($data);

        // first send unsigned long with lenght of serialized data
        $this->writeStream(pack("L", strlen($string)));
        // then send data
        $this->writeStream($string);
    }

    private function writeStream($string)
    {
        $written = 0;
        $length = strlen($string);

        while ($written < $length) {
            $packet = substr(
                $string,
                $written,
                min($length - $written, 1024)
            );

            $fwrite = fwrite(
                $this->stream,
                $packet,
                strlen($packet)
            );

            if ($fwrite === false) {
                throw new \Exception();
            }

            $written += $fwrite;
        }
    }

    private function readStream($length)
    {
        $buff = '';

        while (strlen($buff) < $length) {
            $fread = fread(
                $this->stream,
                min($length, 1024)
            );

            if ($fread === false) {
                throw new \Exception();
            }

            $buff .= $fread;
        }

        return $buff;
    }

    public function read()
    {
        if ($this->stream === null) {
            throw new \Exception("Trying to read from closed channel");
        }

        // first read unsigned long as lenght of serialized data
        $length = unpack("Llong", $this->readStream(4));

        // then read serialized data with that length
        return unserialize(
            $this->readStream($length['long'])
        );
    }

    public function close()
    {
        if (!@fclose($this->stream)) {
            throw new \Exception("Could not close channel");
        }

        $this->stream = null;
    }

    public function eof()
    {
        return $this->stream !== null && !feof($this->stream);
    }

    public function __destruct()
    {
        if ($this->stream !== null) {
            $this->close();
        }
    }
}
