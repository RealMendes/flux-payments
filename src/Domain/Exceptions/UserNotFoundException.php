<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\DomainException\DomainRecordNotFoundException;

class UserNotFoundException extends DomainRecordNotFoundException
{
    public function __construct(string $identifier = '', string $message = '')
    {
        if (empty($message)) {
            $message = 'Usuário não encontrado';
            if (!empty($identifier)) {
                $message .= ': ' . $identifier;
            }
        }
        
        parent::__construct($message);
    }
    
    public static function byId(int $id): self
    {
        return new self("ID {$id}");
    }
    
    public static function byEmail(string $email): self
    {
        return new self("E-mail {$email}");
    }
    
    public static function byCpfCnpj(string $cpfCnpj): self
    {
        return new self("CPF/CNPJ {$cpfCnpj}");
    }
    
    public static function withoutWallet(int $userId): self
    {
        return new self('', "Usuário ID {$userId} não possui carteira");
    }
}
