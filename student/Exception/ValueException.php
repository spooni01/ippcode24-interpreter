<?php
/**
 * IPP - PHP Project Student
 * Inspired from IPP/EXCEPTION/IPPException.php
 */

namespace IPP\Student\Exception;

use IPP\Core\ReturnCode;
use Exception;
use Throwable;

/**
 * Exception for an invalid source structure
 */
class ValueException extends Exception
{
    public function __construct(string $message = "Invalid semantic.", ?Throwable $previous = null) /** @phpstan-ignore-line */ 
    {
        parent::__construct($message, ReturnCode::VALUE_ERROR, $previous);
    }
}
