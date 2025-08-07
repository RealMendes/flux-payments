<?php

declare(strict_types=1);

namespace Tests\Domain\ValueObjects;

use App\Domain\ValueObjects\UserType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserTypeTest extends TestCase
{
    public function testValidCommonUserType(): void
    {
        $userType = new UserType('COMMON');
        
        $this->assertEquals('COMMON', $userType->getValue());
    }

    public function testValidMerchantUserType(): void
    {
        $userType = new UserType('MERCHANT');
        
        $this->assertEquals('MERCHANT', $userType->getValue());
    }

    public function testUserTypeToString(): void
    {
        $userType = new UserType('COMMON');
        
        $this->assertEquals('COMMON', (string) $userType);
    }    public function testInvalidUserTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo deve ser COMMON ou MERCHANT');
        
        new UserType('INVALID');
    }

    public function testEmptyUserTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo deve ser COMMON ou MERCHANT');
        
        new UserType('');
    }

    public function testLowercaseUserTypeIsNormalized(): void
    {
        $userType = new UserType('common');
        
        $this->assertEquals('COMMON', $userType->getValue());
    }

    public function testMixedCaseUserTypeIsNormalized(): void
    {
        $userType = new UserType('Merchant');
        
        $this->assertEquals('MERCHANT', $userType->getValue());
    }

    public function testUserTypeEquality(): void
    {
        $userType1 = new UserType('COMMON');
        $userType2 = new UserType('common');
        $userType3 = new UserType('MERCHANT');
        
        $this->assertTrue($userType1->equals($userType2));
        $this->assertFalse($userType1->equals($userType3));
    }

    public function testIsCommon(): void
    {
        $commonUser = new UserType('COMMON');        $merchantUser = new UserType('MERCHANT');
        
        $this->assertTrue($commonUser->isCommon());
        $this->assertFalse($merchantUser->isCommon());
    }

    public function testIsMerchant(): void
    {
        $commonUser = new UserType('COMMON');
        $merchantUser = new UserType('MERCHANT');
        
        $this->assertFalse($commonUser->isMerchant());
        $this->assertTrue($merchantUser->isMerchant());
    }
}
