<?php

namespace CreditBundle\Tests\Unit\Exception;

use CreditBundle\Exception\CreditInsufficientException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CreditInsufficientExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new CreditInsufficientException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Insufficient credit balance';
        $exception = new CreditInsufficientException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Insufficient credit balance';
        $code = 3001;
        $exception = new CreditInsufficientException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}