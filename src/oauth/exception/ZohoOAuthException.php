<?php

namespace zcrmsdk\oauth\exception;

use Exception;
use Throwable;

class ZohoOAuthException extends Exception
{

    protected $message = 'Unknown exception';

    // Exception message
    private $string;

    // Source line of exception
    private $trace;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if ( ! $message) {
            throw new $this('Unknown '.get_class($this));
        }
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return get_class($this)." Caused by:'{$this->message}' in {$this->file}({$this->line})\n"
               ."{$this->getTraceAsString()}";
    }
}