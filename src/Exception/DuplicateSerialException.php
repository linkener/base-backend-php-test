<?php


namespace App\Exception;


use Throwable;

class DuplicateSerialException extends \Exception
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(
            'save meter failed because of duplicate serial',
            0,
            $previous
        );
    }
}
