<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

interface WalletRepository
{
    /**
     * @param int $userId
     * @return Wallet
     * @throws WalletNotFoundException
     */
    public function findByUserId(int $userId): Wallet;

    /**
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function updateBalance(int $userId, float $amount): bool;

    /**
     * @param Wallet $wallet
     * @return Wallet
     */
    public function save(Wallet $wallet): Wallet;
}
