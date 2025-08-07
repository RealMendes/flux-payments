<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Transaction Amount Value Object
 * 
 * Represents a monetary amount for transactions with validation
 */
final class TransactionAmount
{
    private readonly float $value;

    public const MIN_VALUE = 0.01;
    public const MAX_VALUE = 999999.99;

    public function __construct(float $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('O valor da transação deve ser positivo');
        }

        if ($value > self::MAX_VALUE) {
            throw new InvalidArgumentException('O valor da transação excede o limite máximo');
        }

        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function getFormattedValue(): string
    {
        return number_format($this->value, 2, ',', '.');
    }

    public function getCurrency(): string
    {
        return 'R$ ' . $this->getFormattedValue();
    }

    public function equals(TransactionAmount $other): bool
    {
        return abs($this->value - $other->value) < 0.001; // Float comparison with tolerance
    }

    public function isGreaterThan(TransactionAmount $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(TransactionAmount $other): bool
    {
        return $this->value < $other->value;
    }

    public function add(TransactionAmount $other): TransactionAmount
    {
        return new self($this->value + $other->value);
    }

    public function subtract(TransactionAmount $other): TransactionAmount
    {
        return new self($this->value - $other->value);
    }
}
