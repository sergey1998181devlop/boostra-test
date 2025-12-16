<?php

namespace App\Core\Exception\Exceptions;

use App\Core\Exception\BaseException;

class RouterException extends BaseException {
    protected $message = 'This route did not defined';
    protected $code    = 404;
}
