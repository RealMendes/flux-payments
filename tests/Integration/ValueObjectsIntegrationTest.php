<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\DTO\UserRegisterRequestDTO;
use App\Application\DTO\TransactionRequestDTO;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\CpfCnpj;
use App\Domain\ValueObjects\TransactionAmount;
use PHPUnit\Framework\TestCase;

class ValueObjectsIntegrationTest extends TestCase
{
    public function testUserRegisterDTOWithValueObjects(): void
    {
        $dto = new UserRegisterRequestDTO(
            'João da Silva Santos',
            '11144477735',
            'joao.silva@example.com',
            'senhaSegura123',
            'COMMON'
        );

        // Verifica se os Value Objects foram criados corretamente
        $this->assertInstanceOf(Email::class, $dto->getEmail());
        $this->assertInstanceOf(CpfCnpj::class, $dto->getCpfCnpj());

        $this->assertEquals('joao.silva@example.com', $dto->getEmail()->getValue());
        $this->assertEquals('11144477735', $dto->getCpfCnpj()->getValue());
        $this->assertEquals('111.444.777-35', $dto->getCpfCnpj()->getFormatted());
        $this->assertTrue($dto->getCpfCnpj()->isCpf());
        $this->assertTrue($dto->getType()->isCommon());
    }

    public function testTransactionDTOWithValueObjects(): void
    {
        $dto = new TransactionRequestDTO(
            250.75,
            1,
            2
        );

        // Verifica se os Value Objects foram criados corretamente
        $this->assertInstanceOf(TransactionAmount::class, $dto->getAmount());

        $this->assertEquals(250.75, $dto->getAmount()->getValue());
        $this->assertEquals('R$ 250,75', $dto->getAmount()->getCurrency());
        $this->assertEquals('250,75', $dto->getAmount()->getFormattedValue());

        $this->assertEquals(1, $dto->getParticipants()->getPayerId()->getValue());
        $this->assertEquals(2, $dto->getParticipants()->getPayeeId()->getValue());
    }

    public function testValueObjectsEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));

        $amount1 = new TransactionAmount(100.50);
        $amount2 = new TransactionAmount(100.50);
        $amount3 = new TransactionAmount(200.00);

        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
    }

    public function testValueObjectsArithmetic(): void
    {
        $amount1 = new TransactionAmount(100.00);
        $amount2 = new TransactionAmount(50.00);

        $sum = $amount1->add($amount2);
        $this->assertEquals(150.00, $sum->getValue());

        $difference = $amount1->subtract($amount2);
        $this->assertEquals(50.00, $difference->getValue());

        $this->assertTrue($amount1->isGreaterThan($amount2));
        $this->assertFalse($amount2->isGreaterThan($amount1));
    }

    public function testCnpjFormatting(): void
    {
        $dto = new UserRegisterRequestDTO(
            'Empresa Ltda',
            '11222333000181',
            'contato@empresa.com',
            'senhaEmpresa123',
            'MERCHANT'
        );

        $this->assertTrue($dto->getCpfCnpj()->isCnpj());
        $this->assertEquals('11.222.333/0001-81', $dto->getCpfCnpj()->getFormatted());
        $this->assertTrue($dto->getType()->isMerchant());
    }
}
