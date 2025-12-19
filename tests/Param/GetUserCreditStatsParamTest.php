<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Param;

use CreditBundle\Param\GetUserCreditStatsParam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * @internal
 */
#[CoversClass(GetUserCreditStatsParam::class)]
final class GetUserCreditStatsParamTest extends TestCase
{
    public function testParamCanBeConstructed(): void
    {
        $param = new GetUserCreditStatsParam(currency: 'CNY');

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('CNY', $param->currency);
    }

    public function testParamIsReadonly(): void
    {
        $param = new GetUserCreditStatsParam(currency: 'USD');

        $this->assertSame('USD', $param->currency);
    }
}
