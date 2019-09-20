<?php

namespace SubProcess\Guards;

use InvalidArgumentException;

final class TypeGuard
{
    /**
     * @param mixed $value
     * @return string
     */
    private static function getType($value)
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }

    /**
     * @param mixed $value
     * @param string $format
     * @return InvalidArgumentException
     */
    private static function exceptionFor($value, $format)
    {
        $message = \sprintf($format, self::getType($value));
        return new InvalidArgumentException($message);
    }

    /**
     * @param mixed $value
     * @param string $messageFormat
     * @throws InvalidArgumentException
     */
    public static function assertCallable($value, $messageFormat = "Expected callable but %s given")
    {
        if (!is_callable($value)) {
            throw self::exceptionFor($value, $messageFormat);
        }
    }

    /**
     * @param mixed $value
     * @param string $messageFormat
     * @throws InvalidArgumentException
     */
    public static function assertString($value, $messageFormat = "Expected string but %s given")
    {
        if (!is_string($value)) {
            throw self::exceptionFor($value, $messageFormat);
        }
    }

    /**
     * @param mixed $value
     * @param string $messageFormat
     * @throws InvalidArgumentException
     */
    public static function assertInt($value, $messageFormat = "Expected integer but %s given")
    {
        if (!is_int($value)) {
            throw self::exceptionFor($value, $messageFormat);
        }
    }

    /**
     * @param mixed $value
     * @param string $messageFormat
     * @throws InvalidArgumentException
     */
    public static function assertIntNullable($value, $messageFormat = "Expected integer or null but %s given")
    {
        if (!is_int($value) && !is_null($value)) {
            throw self::exceptionFor($value, $messageFormat);
        }
    }

    /**
     * @param string $value
     * @param string $messageFormat
     * @throws InvalidArgumentException
     */
    public static function assertResource($value, $messageFormat = "Expected integer or null but %s given")
    {
        if (!\is_resource($value)) {
            throw self::exceptionFor($value, $messageFormat);
        }
    }
}
