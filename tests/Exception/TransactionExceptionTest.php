<?php

namespace CreditBundle\Tests\Exception;

use CreditBundle\Exception\TransactionException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(TransactionException::class)]
final class TransactionExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsException(): void
    {
        $exception = new TransactionException();
        $this->assertInstanceOf(\Exception::class, $exception);
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
