<?php

namespace SubProcess\IPC;

use SubProcess\Guards\TypeGuard;

class StringBuffer
{
    /** @var string */
    private $buffer;

    /**
     * @param string $data
     */
    public function __construct($data = '')
    {
        TypeGuard::assertString($data);
        $this->buffer = $data;
    }

    /**
     * @param string $data
     */
    public function append($data)
    {
        TypeGuard::assertString($data);
        $this->buffer .= $data;
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @return string
     */
    public function read($offset = 0, $length = null)
    {
        TypeGuard::assertInt($offset);
        TypeGuard::assertIntNullable($length);

        if ($length === null) {
            $length = \strlen($this->buffer) - $offset;
        }

        return \substr($this->buffer, $offset, $length);
    }

    /**
     * @param int $offset
     * @param int $length
     */
    public function remove($offset, $length)
    {
        TypeGuard::assertInt($offset);
        TypeGuard::assertInt($length);

        if ($offset ===0 && $length === $this->size()) {
            $this->buffer = '';
        } elseif ($offset === 0) {
            $this->buffer = \substr($this->buffer, $length);
        } elseif ($offset + $length === strlen($this->buffer)) {
            $this->buffer = \substr($this->buffer, 0, $offset);
        } else {
            $this->buffer = \substr($this->buffer, 0, $offset) . \substr($this->buffer, $offset + $length);
        }
    }

    /**
     * @return int
     */
    public function size()
    {
        return \strlen($this->buffer);
    }
}
