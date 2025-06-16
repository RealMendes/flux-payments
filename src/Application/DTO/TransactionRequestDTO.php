<?php

declare(strict_types=1);

namespace App\Application\DTO;

class TransactionRequestDTO
{
    private float $value;
    private int $payerId;
    private int $payeeId;
    public function __construct(
        float $value,
        int $payerId,
        int $payeeId
    ) {
        $this->validateTransactionData($value, $payerId, $payeeId);
        
        $this->value = $value;
        $this->payerId = $payerId;
        $this->payeeId = $payeeId;
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

    private function validateTransactionData(float $value, int $payerId, int $payeeId): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('O valor da transação deve ser positivo');
        }

        if ($value > 999999.99) {
            throw new \InvalidArgumentException('O valor da transação excede o limite máximo');
        }

        if ($payerId <= 0) {
            throw new \InvalidArgumentException('ID do pagador inválido');
        }

        if ($payeeId <= 0) {
            throw new \InvalidArgumentException('ID do recebedor inválido');
        }

        if ($payerId === $payeeId) {
            throw new \InvalidArgumentException('Não é possível realizar transação para o mesmo usuário');
        }
    }
}
