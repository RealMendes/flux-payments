<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;
use DateTime;

class User implements JsonSerializable
{
    public const TYPE_COMMON = 'COMMON';
    public const TYPE_MERCHANT = 'MERCHANT';

    private ?int $id;
    private string $fullName;
    private string $cpfCnpj;
    private string $email;
    private string $password;
    private string $type;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $password,
        string $type = self::TYPE_COMMON,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->cpfCnpj = $cpfCnpj;
        $this->email = $email;
        $this->password = $password;
        $this->type = $type;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getCpfCnpj(): string
    {
        return $this->cpfCnpj;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function isCommon(): bool
    {
        return $this->type === self::TYPE_COMMON;
    }

    public function isMerchant(): bool
    {
        return $this->type === self::TYPE_MERCHANT;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName,
            'cpf_cnpj' => $this->cpfCnpj,
            'email' => $this->email,
            'type' => $this->type,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
