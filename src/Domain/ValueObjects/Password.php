<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Respect\Validation\Validator as v;
use InvalidArgumentException;

/**
 * Password Value Object
 * 
 * Represents a password with validation and secure hashing
 */
final class Password
{
    private readonly string $value;

    public function __construct(string $password)
    {
        if (!v::stringType()->length(6, null)->validate($password)) {
            throw new InvalidArgumentException('Senha deve ter pelo menos 6 caracteres');
        }
        
        $this->value = $password;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getHashed(): string
    {
        return password_hash($this->value, PASSWORD_ARGON2ID);
    }

    public function verify(string $hashedPassword): bool
    {
        return password_verify($this->value, $hashedPassword);
    }

    public function equals(Password $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return '[PROTECTED]';
    }
}
