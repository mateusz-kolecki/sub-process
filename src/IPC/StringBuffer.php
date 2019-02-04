<?php

namespace SubProcess\IPC;

class StringBuffer
{
    /** @var string */
    private $buffer = '';

    public function append($data)
    {
        $this->buffer .= $data;
    }
    
    public function read($offset, $length = null)
    {
        if ($length === null) {
            $length = strlen($this->buffer) - $offset;
        }
        
        return \substr($this->buffer, $offset, $length);
    }
    
    public function remove($offset, $length)
    {
        if ($offset === 0) {
            $this->buffer = \substr($this->buffer, $length);
        } elseif ($offset + $length === strlen($this->buffer)) {
            $this->buffer = \substr($this->buffer, 0, $offset);
        } else {
            $this->buffer = \substr($this->buffer, 0, $offset) . \substr($this->buffer, $offset + $length);
        }
    }
    
    public function size()
    {
        return strlen($this->buffer);
    }
}
