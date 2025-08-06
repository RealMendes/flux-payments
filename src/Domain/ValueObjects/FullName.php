<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Respect\Validation\Validator as v;
use InvalidArgumentException;

/**
 * Full Name Value Object
 * 
 * Represents a person's full name with validation and normalization
 */
final class FullName
{
    private readonly string $value;

    public function __construct(string $fullName)
    {
        $normalizedName = $this->normalize($fullName);
        
        if (!v::stringType()->notEmpty()->length(2, null)->validate($normalizedName)) {
            throw new InvalidArgumentException('Nome completo deve ter pelo menos 2 caracteres');
        }
        
        $this->value = $normalizedName;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFirstName(): string
    {
        $parts = explode(' ', $this->value);
        return $parts[0] ?? '';
    }

    public function getLastName(): string
    {
        $parts = explode(' ', $this->value);
        
        if (count($parts) === 1) {
            return '';
        }
        
        return end($parts);
    }

    public function equals(FullName $other): bool
    {
        return $this->value === $other->value;
    }

    private function normalize(string $fullName): string
    {
        return trim($fullName);
    }
}
