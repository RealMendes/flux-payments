<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\CpfCnpj;
use App\Domain\ValueObjects\FullName;
use App\Domain\ValueObjects\Password;
use App\Domain\ValueObjects\UserType;

class UserRegisterRequestDTO
{
    private FullName $fullName;
    private CpfCnpj $cpfCnpj;
    private Email $email;
    private Password $password;
    private UserType $type;

    public function __construct(
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $password,
        string $type
    ) {
        $this->fullName = new FullName($fullName);
        $this->cpfCnpj = new CpfCnpj($cpfCnpj);
        $this->email = new Email($email);
        $this->password = new Password($password);
        $this->type = new UserType($type);
    }
    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public function getCpfCnpj(): CpfCnpj
    {
        return $this->cpfCnpj;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getType(): UserType
    {
        return $this->type;
    }
}
