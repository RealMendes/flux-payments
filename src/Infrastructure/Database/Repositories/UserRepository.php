<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepository as UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseConnection;
use PDO;
use DateTime;
use App\Application\Settings\SettingsInterface;

class UserRepository implements UserRepositoryInterface
{
    private PDO $database;

    public function __construct(SettingsInterface $settings)
    {
        $this->database = DatabaseConnection::getConnection($settings);
    }

    public function findAll(): array
    {
        $statement = $this->database->prepare('SELECT * FROM users ORDER BY id');
        $statement->execute();
        
        $users = [];
        while ($row = $statement->fetch()) {
            $users[] = $this->hydrate($row);
        }
        
        return $users;
    }

    public function findUserOfId(int $id): User
    {
        return $this->findById($id);
    }

    public function findById(int $id): User
    {
        $statement = $this->database->prepare('SELECT * FROM users WHERE id = :id');
        $statement->bindParam(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        
        $row = $statement->fetch();
        if (!$row) {
            throw new UserNotFoundException();
        }
        
        return $this->hydrate($row);
    }

    public function findByCpfCnpj(string $cpfCnpj): User
    {
        $statement = $this->database->prepare('SELECT * FROM users WHERE cpf_cnpj = :cpf_cnpj');
        $statement->bindParam(':cpf_cnpj', $cpfCnpj, PDO::PARAM_STR);
        $statement->execute();
        
        $row = $statement->fetch();
        if (!$row) {
            throw new UserNotFoundException();
        }
        
        return $this->hydrate($row);
    }

    public function findByEmail(string $email): User
    {
        $statement = $this->database->prepare('SELECT * FROM users WHERE email = :email');
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        
        $row = $statement->fetch();
        if (!$row) {
            throw new UserNotFoundException();
        }
        
        return $this->hydrate($row);
    }

    public function save(User $user): User
    {
        if ($user->getId() === null) {
            return $this->insert($user);
        } else {
            return $this->update($user);
        }
    }

    private function insert(User $user): User
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (full_name, cpf_cnpj, email, password, type, created_at, updated_at) 
             VALUES (:full_name, :cpf_cnpj, :email, :password, :type, :created_at, :updated_at)'
        );
        
        $createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');
        $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
        
        $fullName = $user->getFullName();
        $cpfCnpj = $user->getCpfCnpj();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $type = $user->getType();

        $statement->bindParam(':full_name', $fullName, PDO::PARAM_STR);
        $statement->bindParam(':cpf_cnpj', $cpfCnpj, PDO::PARAM_STR);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->bindParam(':password', $password, PDO::PARAM_STR);
        $statement->bindParam(':type', $type, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $createdAt, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        
        $statement->execute();
        
        $id = (int) $this->database->lastInsertId();
        return $this->findById($id);
    }

    private function update(User $user): User
    {
        $statement = $this->database->prepare(
            'UPDATE users 
             SET full_name = :full_name, cpf_cnpj = :cpf_cnpj, email = :email, 
                 password = :password, type = :type, updated_at = :updated_at 
             WHERE id = :id'
        );
        
        $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
        
        $statement->bindParam(':id', $user->getId(), PDO::PARAM_INT);
        $statement->bindParam(':full_name', $user->getFullName(), PDO::PARAM_STR);
        $statement->bindParam(':cpf_cnpj', $user->getCpfCnpj(), PDO::PARAM_STR);
        $statement->bindParam(':email', $user->getEmail(), PDO::PARAM_STR);
        $statement->bindParam(':password', $user->getPassword(), PDO::PARAM_STR);
        $statement->bindParam(':type', $user->getType(), PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $updatedAt, PDO::PARAM_STR);
        
        $statement->execute();
        
        return $this->findById($user->getId());
    }

    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['full_name'],
            $row['cpf_cnpj'],
            $row['email'],
            $row['password'],
            $row['type'],
            new DateTime($row['created_at']),
            new DateTime($row['updated_at'])
        );
    }
}