<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Application\DTO\UserRegisterRequestDTO;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\CpfCnpj;
use App\Domain\Wallet\Wallet;
use App\Domain\Wallet\WalletRepository;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\User\UserNotFoundException;

class UserService implements UserManagementService
{
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;

    public function __construct(
        UserRepository $userRepository,
        WalletRepository $walletRepository
    ) {
        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
    }

    /**
     * Registra um novo usuário no sistema
     *
     * @param UserRegisterRequestDTO $dto
     * @return User
     * @throws UserAlreadyExistsException
     * @throws \Exception
     */
    public function registerUser(UserRegisterRequestDTO $dto): User
    {
        $this->validateUserUniqueness($dto->getEmail(), $dto->getCpfCnpj());

        $hashedPassword = $dto->getPassword()->getHashed();

        $user = new User(
            null,
            $dto->getFullName()->getValue(),
            $dto->getCpfCnpj()->getValue(),
            $dto->getEmail()->getValue(),
            $hashedPassword,
            $dto->getType()->getValue()
        );

        try {
            $savedUser = $this->userRepository->save($user);

            if ($savedUser->getId() === null) {
                throw new \Exception('Erro ao salvar usuário - ID não gerado');
            }

            $wallet = new Wallet(
                null,
                $savedUser->getId(),
                10.00,
            );

            $this->walletRepository->save($wallet);

            return $savedUser;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao registrar usuário: ' . $e->getMessage());
        }
    }

    private function findUserByEmail(string $email): ?User
    {
        try {
            return $this->userRepository->findByEmail($email);
        } catch (UserNotFoundException $e) {
            return null;
        }
    }

    private function findUserByCpfCnpj(string $cpfCnpj): ?User
    {
        try {
            return $this->userRepository->findByCpfCnpj($cpfCnpj);
        } catch (UserNotFoundException $e) {
            return null;
        }
    }

    /**
     * Valida se um usuário já existe no sistema
     *
     * @param Email $email
     * @param CpfCnpj $cpfCnpj
     * @throws UserAlreadyExistsException
     */
    private function validateUserUniqueness(Email $email, CpfCnpj $cpfCnpj): void
    {
        if ($this->userExistsByEmail($email->getValue())) {
            throw UserAlreadyExistsException::byEmail($email->getValue());
        }

        if ($this->userExistsByCpfCnpj($cpfCnpj->getValue())) {
            throw UserAlreadyExistsException::byCpfCnpj($cpfCnpj->getValue());
        }
    }

    /**
     * Verifica se existe um usuário com o email informado
     *
     * @param string $email
     * @return bool
     */
    private function userExistsByEmail(string $email): bool
    {
        return $this->findUserByEmail($email) !== null;
    }

    /**
     * Verifica se existe um usuário com o CPF/CNPJ informado
     *
     * @param string $cpfCnpj
     * @return bool
     */
    private function userExistsByCpfCnpj(string $cpfCnpj): bool
    {
        return $this->findUserByCpfCnpj($cpfCnpj) !== null;
    }
}
