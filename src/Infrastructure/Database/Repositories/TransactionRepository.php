<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionNotFoundException;
use App\Domain\Transaction\TransactionRepository as TransactionRepositoryInterface;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;
use DateTime;
use App\Application\Settings\SettingsInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    private PDO $database;

    public function __construct(SettingsInterface $settings)
    {
        $this->database = DatabaseConnection::getConnection($settings);
    }

    public function save(Transaction $transaction): Transaction
    {
        if ($transaction->getId() === null) {
            return $this->insert($transaction);
        } else {
            return $this->update($transaction);
        }
    }

    public function findById(int $id): Transaction
    {
        $statement = $this->database->prepare('SELECT * FROM transactions WHERE id = :id');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        
        $row = $statement->fetch();
        if (!$row) {
            throw new TransactionNotFoundException();
        }
        
        return $this->hydrate($row);
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->database->prepare(
            'SELECT * FROM transactions WHERE payer_id = :user_id OR payee_id = :user_id ORDER BY created_at DESC'
        );
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        $transactions = [];
        while ($row = $statement->fetch()) {
            $transactions[] = $this->hydrate($row);
        }
        
        return $transactions;
    }

    public function findByPayerId(int $payerId): array
    {        
        $statement = $this->database->prepare('SELECT * FROM transactions WHERE payer_id = :payer_id ORDER BY created_at DESC');
        $statement->bindParam(':payer_id', $payerId, PDO::PARAM_INT);
        $statement->execute();
        
        $transactions = [];
        while ($row = $statement->fetch()) {
            $transactions[] = $this->hydrate($row);
        }
        
        return $transactions;
    }

    public function findByPayeeId(int $payeeId): array
    {        
        $statement = $this->database->prepare('SELECT * FROM transactions WHERE payee_id = :payee_id ORDER BY created_at DESC');
        $statement->bindParam(':payee_id', $payeeId, PDO::PARAM_INT);
        $statement->execute();
        
        $transactions = [];
        while ($row = $statement->fetch()) {
            $transactions[] = $this->hydrate($row);
        }
        
        return $transactions;
    }

    private function insert(Transaction $transaction): Transaction
    {
        $statement = $this->database->prepare('INSERT INTO transactions (value, payer_id, payee_id, status, created_at, updated_at) 
             VALUES (:value, :payer_id, :payee_id, :status, :created_at, :updated_at)'
        );
        $value = $transaction->getValue();
        $payerId = $transaction->getPayerId();
        $payeeId = $transaction->getPayeeId();
        $status = $transaction->getStatus();
        $createdAt = $transaction->getCreatedAt()->format('Y-m-d H:i:s');
        $updatedAt = $transaction->getUpdatedAt()->format('Y-m-d H:i:s');        
        $statement->bindParam(':value', $value, PDO::PARAM_STR);
        $statement->bindParam(':payer_id', $payerId, PDO::PARAM_INT);
        $statement->bindParam(':payee_id', $payeeId, PDO::PARAM_INT);
        $statement->bindParam(':status', $status, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        
        $statement->execute();
        
        $id = (int) $this->database->lastInsertId();
        return $this->findById($id);
    }

    private function update(Transaction $transaction): Transaction
    {        
        $statement = $this->database->prepare(
            'UPDATE transactions 
             SET value = :value, payer_id = :payer_id, payee_id = :payee_id, 
                 status = :status, updated_at = :updated_at 
             WHERE id = :id'
        );
        $transactionId = $transaction->getId();
        $value = $transaction->getValue();
        $payerId = $transaction->getPayerId();
        $payeeId = $transaction->getPayeeId();
        $status = $transaction->getStatus();
        $updatedAt = $transaction->getUpdatedAt()->format('Y-m-d H:i:s');        
        $statement->bindParam(':id', $transactionId, PDO::PARAM_INT);
        $statement->bindParam(':value', $value, PDO::PARAM_STR);
        $statement->bindParam(':payer_id', $payerId, PDO::PARAM_INT);
        $statement->bindParam(':payee_id', $payeeId, PDO::PARAM_INT);
        $statement->bindParam(':status', $status, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        
        $statement->execute();
        
        return $this->findById($transaction->getId());
    }

    private function hydrate(array $row): Transaction
    {        
        return new Transaction(
            (int) $row['id'],
            (float) $row['value'],
            (int) $row['payer_id'],
            (int) $row['payee_id'],
            $row['status'],
            new DateTime($row['created_at']),
            new DateTime($row['updated_at'])
        );
    }
}
