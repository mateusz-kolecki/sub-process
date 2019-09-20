<?php

namespace SubProcess\IPC\Stream;

use SubProcess\Guards\TypeGuard;
use SubProcess\IPC\Stream;
use SubProcess\IPC\StringBuffer;

class ResourceStream implements Stream
{
    /** @var resource */
    private $resource;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        TypeGuard::assertResource($resource);

        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
    {
        TypeGuard::assertString($data);

        if ($this->resource === null) {
            throw new \Exception("Tried to write to closed stream");
        }

        $buffer = new StringBuffer($data);

        while ($buffer->size() > 0) {
            $packet = $buffer->read(0, min($buffer->size(), 1024));

            $bytesSent = \fwrite($this->resource, $packet, \strlen($packet));

            if ($bytesSent === false) {
                throw new \Exception();
            }

            $buffer->remove(0, $bytesSent);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        TypeGuard::assertInt($length);

        if ($this->resource === null) {
            throw new \Exception("Tried to read from closed stream");
        }

        $buffer = new StringBuffer();

        while ($buffer->size() < $length) {
            $chunk = \fread($this->resource, \min($length, 1024));

            if ($chunk === false) {
                throw new \Exception();
            }

            $buffer->append($chunk);
        }

        return $buffer->read();
    }

    public function close()
    {
        if ($this->resource === null) {
            return;
        }

        if (!@fclose($this->resource)) {
            throw new \Exception("Could not close stream");
        }

        $this->resource = null;
    }

    public function eof()
    {
        return $this->resource === null || \feof($this->resource);
    }

    public function resource()
    {
        return $this->resource;
    }

    public function __destruct()
    {
        if ($this->resource === null) {
            return;
        }

        try {
            $this->close();
        } catch (\Exception $e) {
            // noop
        }
    }
}
