<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObjects;

use App\Domain\ValueObjects\CpfCnpj;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CpfCnpjTest extends TestCase
{
    public function testValidCpfCreation(): void
    {
        $cpf = new CpfCnpj('11144477735'); // CPF válido
        
        $this->assertEquals('11144477735', $cpf->getValue());
        $this->assertEquals('111.444.777-35', $cpf->getFormatted());
    }

    public function testValidCnpjCreation(): void
    {
        $cnpj = new CpfCnpj('11222333000181'); // CNPJ válido
        
        $this->assertEquals('11222333000181', $cnpj->getValue());
        $this->assertEquals('11.222.333/0001-81', $cnpj->getFormatted());
    }

    public function testCpfToString(): void
    {
        $cpf = new CpfCnpj('11144477735');
        
        $this->assertEquals('11144477735', (string) $cpf);
    }    public function testInvalidCpfThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF inválido');
        
        new CpfCnpj('12345678901'); // CPF inválido
    }

    public function testInvalidCnpjThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ inválido');
        
        new CpfCnpj('12345678000100'); // CNPJ inválido
    }

    public function testEmptyValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF/CNPJ é obrigatório');
        
        new CpfCnpj('');
    }

    public function testInvalidLengthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
        
        new CpfCnpj('123'); // Muito curto
    }

    public function testCpfCnpjEquality(): void
    {
        $cpf1 = new CpfCnpj('11144477735');
        $cpf2 = new CpfCnpj('11144477735');
        $cpf3 = new CpfCnpj('11222333000181');
        
        $this->assertTrue($cpf1->equals($cpf2));
        $this->assertFalse($cpf1->equals($cpf3));
    }

    public function testFormattedCpfInput(): void
    {
        $cpf = new CpfCnpj('111.444.777-35');
        
        $this->assertEquals('11144477735', $cpf->getValue());
        $this->assertEquals('111.444.777-35', $cpf->getFormatted());
    }

    public function testFormattedCnpjInput(): void
    {
        $cnpj = new CpfCnpj('11.222.333/0001-81');
        
        $this->assertEquals('11222333000181', $cnpj->getValue());
        $this->assertEquals('11.222.333/0001-81', $cnpj->getFormatted());
    }
}
