<?php

declare(strict_types=1);

namespace Tests\Application\DTO;

use App\Application\DTO\UserRegisterRequestDTO;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\CpfCnpj;
use App\Domain\ValueObjects\FullName;
use App\Domain\ValueObjects\Password;
use App\Domain\ValueObjects\UserType;
use PHPUnit\Framework\TestCase;

class UserRegisterRequestDTOTest extends TestCase
{
    public function testValidDTOCreation(): void
    {
        $dto = new UserRegisterRequestDTO(
            'João da Silva',
            '11144477735',
            'joao@example.com',
            'senha123',
            'COMMON'
        );

        $this->assertInstanceOf(FullName::class, $dto->getFullName());
        $this->assertInstanceOf(CpfCnpj::class, $dto->getCpfCnpj());
        $this->assertInstanceOf(Email::class, $dto->getEmail());
        $this->assertInstanceOf(Password::class, $dto->getPassword());
        $this->assertInstanceOf(UserType::class, $dto->getType());

        $this->assertEquals('João da Silva', $dto->getFullName()->getValue());
        $this->assertEquals('11144477735', $dto->getCpfCnpj()->getValue());
        $this->assertEquals('joao@example.com', $dto->getEmail()->getValue());
        $this->assertEquals('COMMON', $dto->getType()->getValue());
    }

    public function testDTOWithMerchantType(): void
    {
        $dto = new UserRegisterRequestDTO(
            'Empresa Ltda',
            '11222333000181',
            'contato@empresa.com',
            'senhaSegura123',
            'MERCHANT'
        );

        $this->assertEquals('MERCHANT', $dto->getType()->getValue());
        $this->assertTrue($dto->getType()->isMerchant());
    }

    public function testDTOWithInvalidEmailThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserRegisterRequestDTO(
            'João da Silva',
            '11144477735',
            'email-invalido',
            'senha123',
            'COMMON'
        );
    }

    public function testDTOWithInvalidCpfThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserRegisterRequestDTO(
            'João da Silva',
            '12345678901', // CPF inválido
            'joao@example.com',
            'senha123',
            'COMMON'
        );
    }

    public function testDTOWithInvalidUserTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserRegisterRequestDTO(
            'João da Silva',
            '11144477735',
            'joao@example.com',
            'senha123',
            'INVALID_TYPE'
        );
    }

    public function testDTOWithShortPasswordThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserRegisterRequestDTO(
            'João da Silva',
            '11144477735',
            'joao@example.com',
            '123', // Senha muito curta
            'COMMON'
        );
    }

    public function testDTOWithEmptyNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UserRegisterRequestDTO(
            '', // Nome vazio
            '11144477735',
            'joao@example.com',
            'senha123',
            'COMMON'
        );
    }

    public function testPasswordIsHashed(): void
    {
        $dto = new UserRegisterRequestDTO(
            'João da Silva',
            '11144477735',
            'joao@example.com',
            'senha123',
            'COMMON'
        );

        $hashedPassword = $dto->getPassword()->getHashed();

        $this->assertNotEquals('senha123', $hashedPassword);
        $this->assertTrue(password_verify('senha123', $hashedPassword));
    }
}
