<?php

declare(strict_types=1);

namespace Tests\Application\DTO;

use App\Application\DTO\TransactionRequestDTO;
use App\Domain\ValueObjects\TransactionAmount;
use App\Domain\ValueObjects\UserId;
use App\Domain\ValueObjects\TransactionParticipants;
use PHPUnit\Framework\TestCase;

class TransactionRequestDTOTest extends TestCase
{
    public function testValidDTOCreation(): void
    {
        $dto = new TransactionRequestDTO(
            100.50,
            1,
            2
        );

        $this->assertInstanceOf(TransactionAmount::class, $dto->getAmount());
        $this->assertInstanceOf(TransactionParticipants::class, $dto->getParticipants());

        $this->assertEquals(100.50, $dto->getAmount()->getValue());
        $this->assertEquals(1, $dto->getParticipants()->getPayerId()->getValue());
        $this->assertEquals(2, $dto->getParticipants()->getPayeeId()->getValue());
    }

    public function testDTOWithMinimumAmount(): void
    {
        $dto = new TransactionRequestDTO(
            0.01,
            1,
            2
        );

        $this->assertEquals(0.01, $dto->getAmount()->getValue());
    }

    public function testDTOWithZeroAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            0.0, // Valor inválido
            1,
            2
        );
    }

    public function testDTOWithNegativeAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            -10.50, // Valor negativo
            1,
            2
        );
    }

    public function testDTOWithSamePayerAndPayeeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            100.50,
            1,
            1 // Mesmo usuário como pagador e recebedor
        );
    }

    public function testDTOWithInvalidPayerIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            100.50,
            0, // ID inválido
            2
        );
    }

    public function testDTOWithInvalidPayeeIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            100.50,
            1,
            -1 // ID inválido
        );
    }

    public function testDTOWithLargeAmount(): void
    {
        $dto = new TransactionRequestDTO(
            99999.99,
            1,
            2
        );

        $this->assertEquals(99999.99, $dto->getAmount()->getValue());
    }

    public function testDTOWithExcessiveAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TransactionRequestDTO(
            1000000.00, // Valor acima do limite
            1,
            2
        );
    }    public function testParticipantsValidation(): void
    {
        $dto = new TransactionRequestDTO(
            100.50,
            5,
            10
        );

        $participants = $dto->getParticipants();
        
        $this->assertEquals(5, $participants->getPayerId()->getValue());
        $this->assertEquals(10, $participants->getPayeeId()->getValue());
        
        // Teste se tem um participante específico
        $payerId = new \App\Domain\ValueObjects\UserId(5);
        $this->assertTrue($participants->hasParticipant($payerId));
    }
}
