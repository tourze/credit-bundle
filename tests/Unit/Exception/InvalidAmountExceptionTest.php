<?php

namespace CreditBundle\Tests\Unit\Exception;

use CreditBundle\Exception\InvalidAmountException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InvalidAmountExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new InvalidAmountException();
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Invalid amount provided';
        $exception = new InvalidAmountException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Invalid amount provided';
        $code = 4001;
        $exception = new InvalidAmountException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}