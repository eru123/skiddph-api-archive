<?php

namespace Api\Database;

use Exception;

class QueryError extends Exception
{
    public function __construct($message = "Error occur", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        $msg = [
            '23000' => 'Duplicate entry', 
        ];

        return static::class . ": " . ($msg[$this->code] ?? $this->message);
    }
}
