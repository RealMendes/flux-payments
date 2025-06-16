<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\DomainException\DomainException;

class UnauthorizedTransactionException extends DomainException
{
    public function __construct(string $reason = '', string $message = '')
    {
        if (empty($message)) {
            $message = 'Transação não autorizada';
            if (!empty($reason)) {
                $message .= ': ' . $reason;
            }
        }
        
        parent::__construct($message, 403);
    }
    
    public static function merchantCannotTransact(): self
    {
        return new self('Lojistas não podem realizar transações');
    }
    
    public static function externalServiceDenied(): self
    {
        return new self('Transação negada pelo serviço de autorização externo');
    }
    
    public static function invalidTransactionAmount(): self
    {
        return new self('Valor da transação inválido');
    }
    
    public static function sameUserTransaction(): self
    {
        return new self('Não é possível realizar transação para o mesmo usuário');
    }
}
