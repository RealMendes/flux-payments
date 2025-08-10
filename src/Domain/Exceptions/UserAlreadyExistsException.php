<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\DomainException\DomainException;

class UserAlreadyExistsException extends DomainException
{
    public function __construct(string $field, string $value, string $message = '')
    {
        if (empty($message)) {
            $message = sprintf('Usuário já existe com %s: %s', $field, $value);
        }

        parent::__construct($message, 409);
    }

    public static function byEmail(string $email): self
    {
        return new self('e-mail', $email);
    }

    public static function byCpfCnpj(string $cpfCnpj): self
    {
        return new self('CPF/CNPJ', $cpfCnpj);
    }

    public static function byEmailAndCpfCnpj(string $email, string $cpfCnpj): self
    {
        return new self(
            'e-mail e CPF/CNPJ',
            "{$email} e {$cpfCnpj}",
            'Usuário já existe com este e-mail e CPF/CNPJ'
        );
    }
}
