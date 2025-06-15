<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Domain\Exceptions\UserNotFoundException;

class WalletService
{
    private WalletRepository $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * Obtém o saldo da carteira de um usuário
     *
     * @param int $userId
     * @return float
     * @throws UserNotFoundException
     */
    public function getBalanceByUserId(int $userId): float
    {        try {
            $wallet = $this->walletRepository->findByUserId($userId);
            return $wallet->getBalance();
        } catch (WalletNotFoundException $e) {
            throw UserNotFoundException::withoutWallet($userId);
        }
    }

    /**
     * Busca a carteira de um usuário
     *
     * @param int $userId
     * @return Wallet
     * @throws UserNotFoundException
     */
    public function getWalletByUserId(int $userId): Wallet
    {        try {
            return $this->walletRepository->findByUserId($userId);
        } catch (WalletNotFoundException $e) {
            throw UserNotFoundException::withoutWallet($userId);
        }
    }

    /**
     * Atualiza o saldo de uma carteira
     *
     * @param Wallet $wallet
     * @return Wallet
     */
    public function updateWallet(Wallet $wallet): Wallet
    {
        return $this->walletRepository->save($wallet);
    }
}
