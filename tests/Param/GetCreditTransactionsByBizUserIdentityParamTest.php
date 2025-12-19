<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Param;

use CreditBundle\Param\GetCreditTransactionsByBizUserIdentityParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(GetCreditTransactionsByBizUserIdentityParam::class)]
final class GetCreditTransactionsByBizUserIdentityParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new GetCreditTransactionsByBizUserIdentityParam(
            startTime: '2024-01-01',
            endTime: '2024-12-31',
            userId: 'user123',
            currentPage: 1,
            pageSize: 10
        );

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('2024-01-01', $param->startTime);
        $this->assertSame('2024-12-31', $param->endTime);
        $this->assertSame('user123', $param->userId);
        $this->assertSame(1, $param->currentPage);
        $this->assertSame(10, $param->pageSize);
    }

    public function testParamWithDefaults(): void
    {
        $param = new GetCreditTransactionsByBizUserIdentityParam();

        $this->assertSame('', $param->startTime);
        $this->assertSame('', $param->endTime);
        $this->assertSame('', $param->userId);
        $this->assertSame(1, $param->currentPage);
        $this->assertSame(10, $param->pageSize);
    }
}
