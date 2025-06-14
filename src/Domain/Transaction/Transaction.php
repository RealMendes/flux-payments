<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use JsonSerializable;
use DateTime;

class Transaction implements JsonSerializable
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';

    private ?int $id;
    private float $value;
    private int $payerId;
    private int $payeeId;
    private string $status;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        ?int $id,
        float $value,
        int $payerId,
        int $payeeId,
        string $status = self::STATUS_PENDING,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->value = $value;
        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getPayerId(): int
    {
        return $this->payerId;
    }

    public function getPayeeId(): int
    {
        return $this->payeeId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTime();
    }

    public function markAsCompleted(): void
    {
        $this->setStatus(self::STATUS_COMPLETED);
    }

    public function markAsFailed(): void
    {
        $this->setStatus(self::STATUS_FAILED);
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
            'value' => $this->value,
            'payer_id' => $this->payerId,
            'payee_id' => $this->payeeId,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
