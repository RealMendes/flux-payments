<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Application\DTO\UserRegisterRequestDTO;

/**
 * Serviço (porta) de gerenciamento de usuários exposto à camada de aplicação.
 */
interface UserManagementService
{
    /**
     * Registra um novo usuário.
     */
    public function registerUser(UserRegisterRequestDTO $dto): User;
}
