<?php

namespace SubProcess;

use SubProcess\Guards\TypeGuard;

class EventEmitter
{
    /** @var callable[][] */
    private $handlers = array();

    /**
     * @param string $eventName
     * @param callable $callback
     */
    public function on($eventName, $callback)
    {
        TypeGuard::assertString($eventName);
        TypeGuard::assertCallable($callback);

        if (!isset($this->handlers[$eventName])) {
            $this->handlers[$eventName] = array();
        }

        $this->handlers[$eventName][] = $callback;
    }

    /**
     * @param string $eventName
     */
    public function emit($eventName/*, ...$args */)
    {
        TypeGuard::assertString($eventName);

        $args = \array_slice(\func_get_args(), 1);

        if (empty($this->handlers[$eventName])) {
            return;
        }

        foreach ($this->handlers[$eventName] as $callback) {
            \call_user_func_array($callback, $args);
        }
    }
}
