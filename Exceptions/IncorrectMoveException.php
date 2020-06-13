<?php

class IncorrectMoveException extends Exception
{
    /**
     * IncorrectMoveException constructor.
     * @param string $message error message
     * @param int $code error code
     * @param Throwable|null $previous previous exception
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}