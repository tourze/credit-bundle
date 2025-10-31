<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AccountService;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AccountService::class)]
#[RunTestsInSeparateProcesses]
final class AccountServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testServiceCreation(): void
    {
        $service = self::getContainer()->get(AccountService::class);

        // 验证服务被正确创建
        self::assertNotNull($service);
    }

    public function testSumIncreasedAmount(): void
    {
        $service = self::getContainer()->get(AccountService::class);
        self::assertInstanceOf(AccountService::class, $service);

        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Account for Sum');
        $account->setIncreasedAmount('100.0');

        $result = $service->sumIncreasedAmount($account);

        self::assertEquals(100.0, $result);
    }
}
