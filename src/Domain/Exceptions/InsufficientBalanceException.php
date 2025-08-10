<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\DomainException\DomainException;

class InsufficientBalanceException extends DomainException
{
    public function __construct(float $currentBalance, float $requiredAmount, string $message = '')
    {
        if (empty($message)) {
            $message = sprintf(
                'Saldo insuficiente. Saldo atual: R$ %.2f, Valor necessário: R$ %.2f',
                $currentBalance,
                $requiredAmount
            );
        }

        parent::__construct($message, 400);
    }
}
