<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Respect\Validation\Validator as v;
use InvalidArgumentException;

/**
 * Email Value Object
 * 
 * Represents an email address with validation and normalization
 */
final class Email
{
    private readonly string $value;

    public function __construct(string $email)
    {
        $normalizedEmail = $this->normalize($email);

        if (!v::email()->validate($normalizedEmail)) {
            throw new InvalidArgumentException('E-mail inválido');
        }

        $this->value = $normalizedEmail;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
