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
class OperandTypeException extends Exception
{
    public function __construct(string $message = "Invalid source structure.", ?Throwable $previous = null)
    {
        parent::__construct($message, ReturnCode::OPERAND_TYPE_ERROR, $previous);
    }
}
