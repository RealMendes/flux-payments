<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\DomainException\DomainException;

class UnauthorizedTransferException extends DomainException
{
    public function __construct(string $reason = '', string $message = '')
    {
        if (empty($message)) {
            $message = 'Transferência não autorizada';
            if (!empty($reason)) {
                $message .= ': ' . $reason;
            }
        }
        
        parent::__construct($message, 403);
    }
    
    public static function merchantCannotTransfer(): self
    {
        return new self('Lojistas não podem realizar transferências');
    }
    
    public static function externalServiceDenied(): self
    {
        return new self('Transferência negada pelo serviço de autorização externo');
    }
    
    public static function invalidTransferAmount(): self
    {
        return new self('Valor da transferência inválido');
    }
    
    public static function sameUserTransfer(): self
    {
        return new self('Não é possível transferir para o mesmo usuário');
    }
}
