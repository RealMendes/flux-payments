<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

interface TransactionRepository
{
    /**
     * @param Transaction $transaction
     * @return Transaction
     */
    public function save(Transaction $transaction): Transaction;

    /**
     * @param int $id
     * @return Transaction
     * @throws TransactionNotFoundException
     */
    public function findById(int $id): Transaction;

    /**
     * @param int $userId
     * @return Transaction[]
     */
    public function findByUserId(int $userId): array;
}
