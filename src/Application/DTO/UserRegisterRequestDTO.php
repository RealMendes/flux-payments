<?php

declare(strict_types=1);

namespace App\Application\DTO;

class UserRegisterRequestDTO
{
    private string $fullName;
    private string $cpfCnpj;
    private string $email;
    private string $password;
    private string $type;    public function __construct(
        string $fullName,
        string $cpfCnpj,
        string $email,
        string $password,
        string $type
    ) {
        $this->validateUserData($fullName, $cpfCnpj, $email, $password, $type);
        
        $this->fullName = trim($fullName);
        $this->cpfCnpj = preg_replace('/\D/', '', $cpfCnpj); // Remove non-digits
        $this->email = strtolower(trim($email));
        $this->password = $password;
        $this->type = strtoupper($type);
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getCpfCnpj(): string
    {
        return $this->cpfCnpj;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getType(): string
    {
        return $this->type;
    }

    private function validateUserData(string $fullName, string $cpfCnpj, string $email, string $password, string $type): void
    {
        // Validação do nome
        if (empty(trim($fullName)) || strlen(trim($fullName)) < 2) {
            throw new \InvalidArgumentException('Nome completo deve ter pelo menos 2 caracteres');
        }

        // Validação do CPF/CNPJ
        $cleanCpfCnpj = preg_replace('/\D/', '', $cpfCnpj);
        if (strlen($cleanCpfCnpj) !== 11 && strlen($cleanCpfCnpj) !== 14) {
            throw new \InvalidArgumentException('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
        }

        // Validação do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail inválido');
        }

        // Validação da senha
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Senha deve ter pelo menos 6 caracteres');
        }

        // Validação do tipo
        if (!in_array(strtoupper($type), ['COMMON', 'MERCHANT'])) {
            throw new \InvalidArgumentException('Tipo deve ser COMMON ou MERCHANT');
        }
    }
}
