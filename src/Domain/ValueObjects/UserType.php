<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Respect\Validation\Validator as v;
use InvalidArgumentException;

/**
 * User Type Value Object
 * 
 * Represents a user type (COMMON or MERCHANT) with validation
 */
final class UserType
{
    private readonly string $value;

    public const COMMON = 'COMMON';
    public const MERCHANT = 'MERCHANT';

    private const VALID_TYPES = [
        self::COMMON,
        self::MERCHANT
    ];

    public function __construct(string $type)
    {
        $normalizedType = $this->normalize($type);
        
        if (!v::in(self::VALID_TYPES)->validate($normalizedType)) {
            throw new InvalidArgumentException('Tipo deve ser COMMON ou MERCHANT');
        }
        
        $this->value = $normalizedType;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isCommon(): bool
    {
        return $this->value === self::COMMON;
    }

    public function isMerchant(): bool
    {
        return $this->value === self::MERCHANT;
    }

    public function equals(UserType $other): bool
    {
        return $this->value === $other->value;
    }

    private function normalize(string $type): string
    {
        return strtoupper(trim($type));
    }
}
