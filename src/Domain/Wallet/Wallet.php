<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use JsonSerializable;
use DateTime;
use App\Domain\Exceptions\InsufficientBalanceException;

class Wallet implements JsonSerializable
{
    private ?int $id;
    private int $userId;
    private float $balance;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        ?int $id,
        int $userId,
        float $balance,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->balance = $balance;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
        $this->updatedAt = new DateTime();
    }

    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
        $this->updatedAt = new DateTime();
    }

    public function subtractBalance(float $amount): void
    {
        $this->balance -= $amount;
        $this->updatedAt = new DateTime();
    }

    public function increaseBalance(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('O valor deve ser positivo');
        }

        $this->balance += $amount;
        $this->updatedAt = new DateTime();
    }
    public function decreaseBalance(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('O valor deve ser positivo');
        }

        if ($this->balance < $amount) {
            throw new InsufficientBalanceException($this->balance, $amount);
        }

        $this->balance -= $amount;
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'balance' => $this->balance,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
