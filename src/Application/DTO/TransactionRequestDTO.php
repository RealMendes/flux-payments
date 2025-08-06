<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObjects\TransactionAmount;
use App\Domain\ValueObjects\UserId;
use App\Domain\ValueObjects\TransactionParticipants;

class TransactionRequestDTO
{
    private TransactionAmount $amount;
    private TransactionParticipants $participants;

    public function __construct(
        float $value,
        int $payerId,
        int $payeeId
    ) {
        $this->amount = new TransactionAmount($value);
        $payerIdVO = new UserId($payerId);
        $payeeIdVO = new UserId($payeeId);
        $this->participants = new TransactionParticipants($payerIdVO, $payeeIdVO);
    }    public function getAmount(): TransactionAmount
    {
        return $this->amount;
    }

    public function getValue(): float
    {
        return $this->amount->getValue();
    }

    public function getPayerId(): int
    {
        return $this->participants->getPayerId()->getValue();
    }

    public function getPayeeId(): int
    {
        return $this->participants->getPayeeId()->getValue();
    }

    public function getParticipants(): TransactionParticipants
    {
        return $this->participants;
    }
}
