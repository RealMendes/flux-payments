<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

/**
 * Porta de gerenciamento de carteiras exposta à aplicação.
 */
interface WalletManagementService
{
    public function getBalanceByUserId(int $userId): float;
    public function getWalletByUserId(int $userId): Wallet;
    public function updateWallet(Wallet $wallet): Wallet;
}
