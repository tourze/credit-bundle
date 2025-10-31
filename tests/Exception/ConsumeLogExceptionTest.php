<?php

namespace CreditBundle\Tests\Exception;

use CreditBundle\Exception\ConsumeLogException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumeLogException::class)]
final class ConsumeLogExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsLogicException(): void
    {
        $exception = new ConsumeLogException();
        $this->assertInstanceOf(\LogicException::class, $exception);
    }

    public function testCanCreateWithMessage(): void
    {
        $message = 'Consume log error';
        $exception = new ConsumeLogException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCanCreateWithMessageAndCode(): void
    {
        $message = 'Consume log error';
        $code = 2001;
        $exception = new ConsumeLogException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
