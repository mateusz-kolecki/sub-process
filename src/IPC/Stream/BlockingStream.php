<?php

namespace SubProcess\IPC\Stream;

use SubProcess\IPC\Stream;

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

        $written = 0;
        $length = strlen($data);

        while ($written < $length) {
            $packet = substr(
                $data,
                $written,
                min($length - $written, 1024)
            );

            $fwrite = fwrite(
                $this->fd,
                $packet,
                strlen($packet)
            );

            if ($fwrite === false) {
                throw new \Exception();
            }

            $written += $fwrite;
        }
    }

    public function read($length)
    {
        if ($this->fd === null) {
            throw new \Exception("Tried to read from closed stream");
        }

        $buff = '';

        while (strlen($buff) < $length) {
            $fread = fread(
                $this->fd,
                min($length, 1024)
            );

            if ($fread === false) {
                throw new \Exception();
            }

            $buff .= $fread;
        }

        return $buff;
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

    public function __destruct()
    {
        if ($this->fd !== null) {
            $this->close();
        }
    }
}
