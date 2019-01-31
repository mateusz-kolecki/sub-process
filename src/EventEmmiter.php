<?php

namespace SubProcess;

class EventEmmiter
{
    private $eventHandlers = [];

    public function on($eventName, $callback)
    {
        if (!isset($this->eventHandlers[$eventName])) {
            $this->eventHandlers[$eventName] = [];
        }

        $this->eventHandlers[$eventName][] = $callback;
    }

    public function emit($eventName, ...$args)
    {
        if (empty($this->eventHandlers[$eventName])) {
            return;
        }

        foreach ($this->eventHandlers[$eventName] as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}
