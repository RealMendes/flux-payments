<?php

declare(strict_types=1);

namespace App\Application\DTO;

class WalletBalanceResponseDTO
{
    private int $userId;
    private float $balance;
    private string $currency;

    public function __construct(int $userId, float $balance, string $currency = 'BRL')
    {
        $this->userId = $userId;
        $this->balance = $balance;
        $this->currency = $currency;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'formatted_balance' => number_format($this->balance, 2, ',', '.')
        ];
    }
}
