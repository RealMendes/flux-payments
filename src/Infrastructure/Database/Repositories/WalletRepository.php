<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Wallet\Wallet;
use App\Domain\Wallet\WalletNotFoundException;
use App\Domain\Wallet\WalletRepository as WalletRepositoryInterface;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;
use DateTime;
use App\Application\Settings\SettingsInterface;

class WalletRepository implements WalletRepositoryInterface
{
    private PDO $database;

    public function __construct(SettingsInterface $settings)
    {
        $this->database = DatabaseConnection::getConnection($settings);
    }

    public function findByUserId(int $userId): Wallet
    {
        $statement = $this->database->prepare('SELECT * FROM wallets WHERE user_id = :user_id');
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();
        if (!$row) {
            throw new WalletNotFoundException();
        }

        return $this->hydrate($row);
    }

    public function updateBalance(int $userId, float $amount): bool
    {
        $statement = $this->database->prepare(
            'UPDATE wallets SET balance = :balance, updated_at = :updated_at WHERE user_id = :user_id'
        );

        $updatedAt = (new DateTime())->format('Y-m-d H:i:s');

        $statement->bindParam(':balance', $amount, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $statement->execute();
    }

    public function save(Wallet $wallet): Wallet
    {
        if ($wallet->getId() === null) {
            return $this->insert($wallet);
        } else {
            return $this->update($wallet);
        }
    }

    public function findById(int $id): Wallet
    {
        $statement = $this->database->prepare('SELECT * FROM wallets WHERE id = :id');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetch();
        if (!$row) {
            throw new WalletNotFoundException();
        }

        return $this->hydrate($row);
    }

    private function insert(Wallet $wallet): Wallet
    {
        $statement = $this->database->prepare(
            'INSERT INTO wallets (user_id, balance, created_at, updated_at) 
             VALUES (:user_id, :balance, :created_at, :updated_at)'
        );
        $userId = $wallet->getUserId();
        $balance = $wallet->getBalance();
        $createdAt = $wallet->getCreatedAt()->format('Y-m-d H:i:s');
        $updatedAt = $wallet->getUpdatedAt()->format('Y-m-d H:i:s');

        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':balance', $balance, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);

        $statement->execute();

        $id = (int) $this->database->lastInsertId();
        return $this->findById($id);
    }

    private function update(Wallet $wallet): Wallet
    {
        $statement = $this->database->prepare(
            'UPDATE wallets 
             SET user_id = :user_id, balance = :balance, updated_at = :updated_at 
             WHERE id = :id'
        );
        $updatedAt = $wallet->getUpdatedAt()->format('Y-m-d H:i:s');
        $walletId = $wallet->getId();

        $statement->bindParam(':id', $walletId, PDO::PARAM_INT);

        $userId = $wallet->getUserId();
        $balance = $wallet->getBalance();

        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':balance', $balance, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);

        $statement->execute();

        return $this->findById($wallet->getId());
    }

    private function hydrate(array $row): Wallet
    {
        return new Wallet(
            (int) $row['id'],
            (int) $row['user_id'],
            (float) $row['balance'],
            new DateTime($row['created_at']),
            new DateTime($row['updated_at'])
        );
    }
}
