<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Param;

use CreditBundle\Param\GetUserCreditTransactionParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(GetUserCreditTransactionParam::class)]
final class GetUserCreditTransactionParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new GetUserCreditTransactionParam(
            startTime: '2024-01-01',
            endTime: '2024-12-31',
            currentPage: 2,
            pageSize: 20
        );

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('2024-01-01', $param->startTime);
        $this->assertSame('2024-12-31', $param->endTime);
        $this->assertSame(2, $param->currentPage);
        $this->assertSame(20, $param->pageSize);
    }

    public function testParamWithDefaults(): void
    {
        $param = new GetUserCreditTransactionParam();

        $this->assertSame('', $param->startTime);
        $this->assertSame('', $param->endTime);
        $this->assertSame(1, $param->currentPage);
        $this->assertSame(10, $param->pageSize);
    }
}
