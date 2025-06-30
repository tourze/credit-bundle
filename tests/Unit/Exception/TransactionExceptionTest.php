<?php

namespace CreditBundle\Tests\Unit\Exception;

use CreditBundle\Exception\TransactionException;
use Exception;
use PHPUnit\Framework\TestCase;

class TransactionExceptionTest extends TestCase
{
    public function testExtendsException(): void
    {
        $exception = new TransactionException();
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Transaction failed';
        $exception = new TransactionException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Transaction failed';
        $code = 5001;
        $exception = new TransactionException($message, $code);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}