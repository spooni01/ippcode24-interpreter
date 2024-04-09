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
class ExitProgramException extends Exception
{

    public int $returnCode;

    public function __construct(string $message = "Invalid semantic.",  int $returnCode, ?Throwable $previous = null) /** @phpstan-ignore-line */ 
    {
        $this->returnCode = $returnCode;
        parent::__construct($message, ReturnCode::OPERAND_VALUE_ERROR, $previous);
    }
}
