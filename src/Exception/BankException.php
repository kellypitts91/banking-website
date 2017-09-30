<?php

namespace agilman\a2\Exception;

use Exception;

/**
 * Class BankException
 *
 * @package kelly_ben/a2
 * @author  Kelly Pitts 09098321 & Ben Wilton 14262032
 */
class BankException extends Exception
{
    private $errorMessage;
    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}