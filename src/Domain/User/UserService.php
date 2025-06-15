<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Application\DTO\UserRegisterRequestDTO;
use App\Domain\Wallet\Wallet;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Exceptions\UserAlreadyExistsException;

class UserService
{
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;

    public function __construct(
        UserRepository $userRepository,
        WalletRepository $walletRepository
    ) {
        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
    }    /**
     * Registra um novo usuário no sistema
     *
     * @param UserRegisterRequestDTO $dto
     * @return User
     * @throws UserAlreadyExistsException
     * @throws \Exception
     */
    public function registerUser(UserRegisterRequestDTO $dto): User
    {
        $hashedPassword = password_hash($dto->getPassword(), PASSWORD_DEFAULT);

        $user = new User(
            null,
            $dto->getFullName(),
            $dto->getCpfCnpj(),
            $dto->getEmail(),
            $hashedPassword,
            $dto->getType()
        );

        try {
            $savedUser = $this->userRepository->save($user);
            
            if ($savedUser->getId() === null) {
                throw new \Exception('Erro ao salvar usuário - ID não gerado');
            }

            $wallet = new Wallet(
                null,
                $savedUser->getId(),
                0.0
            );

            $this->walletRepository->save($wallet);

            return $savedUser;

        } catch (\Exception $e) {
            throw new \Exception('Erro ao registrar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Valida se uma senha corresponde ao hash armazenado
     *
     * @param string $password
     * @param string $hashedPassword
     * @return bool
     */
    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Busca usuário por e-mail
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Busca usuário por CPF/CNPJ
     *
     * @param string $cpfCnpj
     * @return User|null
     */
    public function findUserByCpfCnpj(string $cpfCnpj): ?User
    {
        return $this->userRepository->findByCpfCnpj($cpfCnpj);
    }
}
