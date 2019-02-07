<?php

namespace SubProcess\IPC\Stream;

use SubProcess\IPC\Stream;
use SubProcess\IPC\StringBuffer;

class BlockingStream implements Stream
{
    /** @var resource */
    private $fd;

    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    public function write($data)
    {
        if ($this->fd === null) {
            throw new \Exception("Tried to write to closed stream");
        }

        $buffer = new StringBuffer($data);

        while ($buffer->size() > 0) {
            $packet = $buffer->read(0, min($buffer->size(), 1024));

            $bytesSent = fwrite($this->fd, $packet, \strlen($packet));

            if ($bytesSent === false) {
                throw new \Exception();
            }

            $buffer->remove(0, $bytesSent);
        }
    }

    public function read($length)
    {
        if ($this->fd === null) {
            throw new \Exception("Tried to read from closed stream");
        }

        $buffer = new StringBuffer();

        while ($buffer->size() < $length) {
            $chunk = fread($this->fd, min($length, 1024));

            if ($chunk === false) {
                throw new \Exception();
            }

            $buffer->append($chunk);
        }

        return $buffer->read();
    }

    public function close()
    {
        if ($this->fd === null) {
            return;
        }

        if (!@fclose($this->fd)) {
            throw new \Exception("Could not close stream");
        }

        $this->fd = null;
    }

    public function eof()
    {
        return $this->fd === null || feof($this->fd);
    }

    public function resource()
    {
        return $this->fd;
    }

    public function __destruct()
    {
        if ($this->fd !== null) {
            $this->close();
        }
    }
}
