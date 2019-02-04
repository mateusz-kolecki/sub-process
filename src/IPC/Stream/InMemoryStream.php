<?php

namespace SubProcess\IPC\Stream;

use SubProcess\IPC\Stream;
use SubProcess\IPC\StringBuffer;

/**
 * Use only for testing
 */
class InMemoryStream implements Stream
{

    /** @var boolean */
    private $isOpen = true;

    /** @var StringBuffer */
    private $writeBuffer;

    /** @var string */
    private $readBuffer;

    public function __construct(StringBuffer $readBuffer, StringBuffer $writeBuffer)
    {
        $this->readBuffer = $readBuffer;
        $this->writeBuffer = $writeBuffer;
    }

    public static function createLoop()
    {
        $buffer = new StringBuffer();
        return new self($buffer, $buffer);
    }

    public function read($length)
    {
        if ($this->isOpen === false) {
            throw new \Exception();
        }

        $data = $this->readBuffer->read(0, $length);
        $this->readBuffer->remove(0, $length);

        return $data;
    }

    public function write($data)
    {
        if ($this->isOpen === false) {
            throw new \Exception();
        }

        $this->writeBuffer->append($data);
    }

    public function eof()
    {
        return $this->isOpen === false || $this->readBuffer->size() === 0;
    }

    public function close()
    {
        $this->isOpen = false;
    }
}
