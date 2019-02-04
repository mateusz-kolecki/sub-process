<?php

namespace SubProcess\IPC\Stream;

use SubProcess\IPC\Stream;

/**
 * Use only for testing
 */
class InMemoryStream implements Stream
{

    /** @var boolean */
    private $isOpen = true;

    /** @var string */
    private $writeBuffer;

    /** @var string */
    private $readBuffer;

    public function __construct(&$readBuffer, &$writeBuffer)
    {
        $this->readBuffer = &$readBuffer;
        $this->writeBuffer = &$writeBuffer;
    }

    public static function createLoop()
    {
        $buffer = '';
        return new self($buffer, $buffer);
    }

    public function read($length)
    {
        if ($this->isOpen === false) {
            throw new \Exception();
        }

        $data = \substr($this->readBuffer, 0, $length);
        $this->readBuffer = \substr($this->readBuffer, \strlen($data));

        return $data;
    }

    public function write($data)
    {
        if ($this->isOpen === false) {
            throw new \Exception();
        }

        $this->writeBuffer .= $data;
    }

    public function eof()
    {
        return $this->isOpen === false || 0 === \strlen($this->readBuffer);
    }

    public function close()
    {
        $this->isOpen = false;
    }
}
