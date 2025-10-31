<?php

namespace CreditBundle\Tests\Exception;

use CreditBundle\Exception\InvalidAmountException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidAmountException::class)]
final class InvalidAmountExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new InvalidAmountException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
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
