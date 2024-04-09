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
class FrameAccessException extends Exception
{
    public function __construct(string $message = "Invalid semantic.", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::FRAME_ACCESS_ERROR, $previous);
    }
}
