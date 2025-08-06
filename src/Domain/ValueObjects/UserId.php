<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * User ID Value Object
 * 
 * Represents a user identifier with validation
 */
final class UserId
{
    private readonly int $value;

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID do usuário deve ser um número positivo');
        }

        $this->value = $id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
