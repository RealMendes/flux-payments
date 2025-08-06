<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Transaction Participants Value Object
 * 
 * Represents the payer and payee relationship with validation
 */
final class TransactionParticipants
{
    private readonly UserId $payerId;
    private readonly UserId $payeeId;

    public function __construct(UserId $payerId, UserId $payeeId)
    {
        if ($payerId->equals($payeeId)) {
            throw new InvalidArgumentException('Não é possível realizar transação para o mesmo usuário');
        }

        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
    }

    public function getPayerId(): UserId
    {
        return $this->payerId;
    }

    public function getPayeeId(): UserId
    {
        return $this->payeeId;
    }

    public function equals(TransactionParticipants $other): bool
    {
        return $this->payerId->equals($other->payerId) && 
               $this->payeeId->equals($other->payeeId);
    }

    public function isPayerSameAs(UserId $userId): bool
    {
        return $this->payerId->equals($userId);
    }

    public function isPayeeSameAs(UserId $userId): bool
    {
        return $this->payeeId->equals($userId);
    }

    public function hasParticipant(UserId $userId): bool
    {
        return $this->isPayerSameAs($userId) || $this->isPayeeSameAs($userId);
    }
}
