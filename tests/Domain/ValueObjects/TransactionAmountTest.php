<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObjects;

use App\Domain\ValueObjects\TransactionAmount;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TransactionAmountTest extends TestCase
{
    public function testValidAmountCreation(): void
    {
        $amount = new TransactionAmount(100.50);

        $this->assertEquals(100.50, $amount->getValue());
    }

    public function testAmountToString(): void
    {
        $amount = new TransactionAmount(250.75);

        $this->assertEquals('250.75', (string) $amount);
    }
    public function testZeroAmountThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O valor da transação deve ser positivo');

        new TransactionAmount(0.0);
    }

    public function testNegativeAmountThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O valor da transação deve ser positivo');

        new TransactionAmount(-10.50);
    }

    public function testMinimumValidAmount(): void
    {
        $amount = new TransactionAmount(0.01);

        $this->assertEquals(0.01, $amount->getValue());
    }

    public function testAmountEquality(): void
    {
        $amount1 = new TransactionAmount(100.50);
        $amount2 = new TransactionAmount(100.50);
        $amount3 = new TransactionAmount(200.00);

        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
    }

    public function testAmountFormatting(): void
    {
        $amount = new TransactionAmount(1234.56);

        $this->assertEquals('R$ 1.234,56', $amount->getCurrency());
        $this->assertEquals('1.234,56', $amount->getFormattedValue());
    }

    public function testAmountFormattingWithZeroDecimals(): void
    {
        $amount = new TransactionAmount(1000.00);

        $this->assertEquals('R$ 1.000,00', $amount->getCurrency());
    }

    public function testAmountFormattingSmallValue(): void
    {
        $amount = new TransactionAmount(5.99);

        $this->assertEquals('R$ 5,99', $amount->getCurrency());
    }

    public function testAmountComparison(): void
    {
        $amount1 = new TransactionAmount(100.00);
        $amount2 = new TransactionAmount(150.00);
        $amount3 = new TransactionAmount(50.00);

        $this->assertTrue($amount2->isGreaterThan($amount1));
        $this->assertTrue($amount1->isLessThan($amount2));
        $this->assertTrue($amount1->isGreaterThan($amount3));
        $this->assertFalse($amount1->isGreaterThan($amount2));
    }

    public function testAmountArithmetic(): void
    {
        $amount1 = new TransactionAmount(100.00);
        $amount2 = new TransactionAmount(50.00);

        $sum = $amount1->add($amount2);
        $this->assertEquals(150.00, $sum->getValue());

        $difference = $amount1->subtract($amount2);
        $this->assertEquals(50.00, $difference->getValue());
    }

    public function testMaximumAmountLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O valor da transação excede o limite máximo');

        new TransactionAmount(1000000.00); // Acima do limite
    }
}
