<?php

namespace CreditBundle\Tests\Exception;

use CreditBundle\Exception\CreditInsufficientException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CreditInsufficientException::class)]
final class CreditInsufficientExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new CreditInsufficientException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
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
