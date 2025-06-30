<?php

namespace CreditBundle\Tests\Unit\Exception;

use CreditBundle\Exception\AccountCreationException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AccountCreationExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new AccountCreationException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Account creation failed';
        $exception = new AccountCreationException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Account creation failed';
        $code = 1001;
        $exception = new AccountCreationException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}