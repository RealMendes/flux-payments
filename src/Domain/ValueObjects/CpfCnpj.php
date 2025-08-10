<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Respect\Validation\Validator as v;
use InvalidArgumentException;

/**
 * CPF/CNPJ Value Object
 * 
 * Represents either a CPF (11 digits) or CNPJ (14 digits) with validation and formatting
 */
final class CpfCnpj
{
    private readonly string $value;
    private readonly string $type;

    public const TYPE_CPF = 'CPF';
    public const TYPE_CNPJ = 'CNPJ';

    public function __construct(string $cpfCnpj)
    {
        $cleanValue = $this->clean($cpfCnpj);

        if (empty($cleanValue)) {
            throw new InvalidArgumentException('CPF/CNPJ é obrigatório');
        }

        $length = strlen($cleanValue);

        if ($length === 11) {
            if (!v::cpf()->validate($cleanValue)) {
                throw new InvalidArgumentException('CPF inválido');
            }
            $this->type = self::TYPE_CPF;
        } elseif ($length === 14) {
            if (!v::cnpj()->validate($cleanValue)) {
                throw new InvalidArgumentException('CNPJ inválido');
            }
            $this->type = self::TYPE_CNPJ;
        } else {
            throw new InvalidArgumentException('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
        }

        $this->value = $cleanValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isCpf(): bool
    {
        return $this->type === self::TYPE_CPF;
    }

    public function isCnpj(): bool
    {
        return $this->type === self::TYPE_CNPJ;
    }

    public function getFormatted(): string
    {
        if ($this->isCpf()) {
            return substr($this->value, 0, 3) . '.' .
                substr($this->value, 3, 3) . '.' .
                substr($this->value, 6, 3) . '-' .
                substr($this->value, 9, 2);
        }

        return substr($this->value, 0, 2) . '.' .
            substr($this->value, 2, 3) . '.' .
            substr($this->value, 5, 3) . '/' .
            substr($this->value, 8, 4) . '-' .
            substr($this->value, 12, 2);
    }

    public function equals(CpfCnpj $other): bool
    {
        return $this->value === $other->value;
    }

    private function clean(string $cpfCnpj): string
    {
        return preg_replace('/\D/', '', $cpfCnpj);
    }
}
