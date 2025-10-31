<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Exception;

use CreditBundle\Exception\EntityPropertyNotSetException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(EntityPropertyNotSetException::class)]
final class EntityPropertyNotSetExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new EntityPropertyNotSetException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Test message';
        $exception = new EntityPropertyNotSetException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Test message';
        $code = 123;
        $exception = new EntityPropertyNotSetException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
