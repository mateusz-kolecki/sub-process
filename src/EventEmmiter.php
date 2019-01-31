<?php

namespace SubProcess;

class EventEmmiter
{
    private $eventHandlers = array();

    public function on($eventName, $callback)
    {
        if (!isset($this->eventHandlers[$eventName])) {
            $this->eventHandlers[$eventName] = array();
        }

        $this->eventHandlers[$eventName][] = $callback;
    }

    public function emit($eventName/*, ...$args */)
    {
        $args = array_slice(func_get_args(), 1);

        if (empty($this->eventHandlers[$eventName])) {
            return;
        }

        foreach ($this->eventHandlers[$eventName] as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}
