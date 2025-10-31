<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Entity\Account;
use CreditBundle\Procedure\GetCreditTransactionsByBizUserIdentity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetCreditTransactionsByBizUserIdentity::class)]
#[RunTestsInSeparateProcesses]
final class GetCreditTransactionsByBizUserIdentityTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testConstruct(): void
    {
        $container = self::getContainer();
        $procedure = $container->get(GetCreditTransactionsByBizUserIdentity::class);
        $this->assertInstanceOf(GetCreditTransactionsByBizUserIdentity::class, $procedure);
    }

    public function testExecute(): void
    {
        $container = self::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // 创建测试数据
        $user = $this->createNormalUser('test@example.com', 'password');

        // 创建用户账户
        $account = new Account();
        $account->setName('测试账户');
        $account->setCurrency('CNY');
        $account->setUser($user);
        $account->setEndingBalance(0);
        $account->setIncreasedAmount(0);
        $account->setDecreasedAmount(0);
        $account->setExpiredAmount(0);

        $entityManager->persist($account);
        $entityManager->flush();

        // 获取 Procedure 服务
        $procedure = $container->get(GetCreditTransactionsByBizUserIdentity::class);
        self::assertInstanceOf(GetCreditTransactionsByBizUserIdentity::class, $procedure);
        $procedure->userId = $user->getUserIdentifier();
        $procedure->currentPage = 1;
        $procedure->pageSize = 10;

        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('pagination', $result);

        $pagination = $result['pagination'];
        $this->assertIsArray($pagination, 'Pagination should be an array');
        $this->assertArrayHasKey('current', $pagination);
        $this->assertArrayHasKey('pageSize', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('hasMore', $pagination);
    }
}
