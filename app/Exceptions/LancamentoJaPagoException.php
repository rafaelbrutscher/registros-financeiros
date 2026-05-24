<?php

namespace App\Exceptions;

use RuntimeException;

class LancamentoJaPagoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('O lançamento já está marcado como PAGO.');
    }
}
