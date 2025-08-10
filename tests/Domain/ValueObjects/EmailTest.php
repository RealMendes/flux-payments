<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObjects;

use App\Domain\ValueObjects\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmailCreation(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testEmailToString(): void
    {
        $email = new Email('user@domain.com');

        $this->assertEquals('user@domain.com', (string) $email);
    }
    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('E-mail inválido');

        new Email('invalid-email');
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('E-mail inválido');

        new Email('');
    }

    public function testEmailWithoutDomainThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('E-mail inválido');

        new Email('user@');
    }

    public function testEmailEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function testDifferentCaseEmailsAreEqual(): void
    {
        $email1 = new Email('Test@Example.COM');
        $email2 = new Email('test@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertEquals('test@example.com', $email1->getValue());
    }
}
