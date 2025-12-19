<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Param\GetUserCreditStatsParam;
use CreditBundle\Procedure\GetUserCreditStats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserCreditStats::class)]
#[RunTestsInSeparateProcesses]
final class GetUserCreditStatsTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的设置逻辑，如果需要的话
    }

    public function testConstruct(): void
    {
        $container = self::getContainer();
        $procedure = $container->get(GetUserCreditStats::class);
        $this->assertInstanceOf(GetUserCreditStats::class, $procedure);
    }

    public function testExecute(): void
    {
        // 在没有登录用户的情况下，应该抛出异常
        $container = self::getContainer();
        /** @var GetUserCreditStats $procedure */
        $procedure = $container->get(GetUserCreditStats::class);
        $param = new GetUserCreditStatsParam(currency: 'CNY');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('User should be authenticated');

        $procedure->execute($param);
    }
}
