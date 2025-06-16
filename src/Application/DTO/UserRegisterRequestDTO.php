<?php

declare(strict_types=1);

namespace App\Application\DTO;

use Respect\Validation\Validator as v;

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
    }    private function validateUserData(string $fullName, string $cpfCnpj, string $email, string $password, string $type): void

    {
        if (!v::stringType()->notEmpty()->length(2, null)->validate(trim($fullName))) {
            throw new \InvalidArgumentException('Nome completo deve ter pelo menos 2 caracteres');
        }

        $this->validateCpfCnpj($cpfCnpj);

        if (!v::email()->validate($email)) {
            throw new \InvalidArgumentException('E-mail inválido');
        }

        if (!v::stringType()->length(6, null)->validate($password)) {
            throw new \InvalidArgumentException('Senha deve ter pelo menos 6 caracteres');
        }        
        
        if (!v::in(['COMMON', 'MERCHANT'])->validate(strtoupper($type))) {
            throw new \InvalidArgumentException('Tipo deve ser COMMON ou MERCHANT');
        }
    }

    /**
     * Valida CPF ou CNPJ
     *
     * @param string $cpfCnpj
     * @throws \InvalidArgumentException
     */
    private function validateCpfCnpj(string $cpfCnpj): void
    {
        $cleanCpfCnpj = preg_replace('/\D/', '', $cpfCnpj);
        
        if (empty($cleanCpfCnpj)) {
            throw new \InvalidArgumentException('CPF/CNPJ é obrigatório');
        }

        $length = strlen($cleanCpfCnpj);
        
        if ($length === 11) {
            if (!v::cpf()->validate($cleanCpfCnpj)) {
                throw new \InvalidArgumentException('CPF inválido');
            }
            return;
        }
        
        if ($length === 14) {
            if (!v::cnpj()->validate($cleanCpfCnpj)) {
                throw new \InvalidArgumentException('CNPJ inválido');
            }
            return;
        }
        
        throw new \InvalidArgumentException('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
    }
}
