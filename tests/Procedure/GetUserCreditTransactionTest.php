<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Exception\TransactionException;
use CreditBundle\Procedure\GetUserCreditTransaction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetUserCreditTransaction::class)]
#[RunTestsInSeparateProcesses]
final class GetUserCreditTransactionTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testConstruct(): void
    {
        $container = self::getContainer();
        $procedure = $container->get(GetUserCreditTransaction::class);
        $this->assertInstanceOf(GetUserCreditTransaction::class, $procedure);
    }

    public function testExecute(): void
    {
        // 在没有账户数据的情况下，应该抛出异常
        $container = self::getContainer();

        // 清理可能存在的测试数据
        $entityManager = self::getEntityManager();
        $entityManager->createQuery('DELETE FROM CreditBundle\Entity\Transaction')->execute();
        $entityManager->createQuery('DELETE FROM CreditBundle\Entity\Account')->execute();

        /** @var GetUserCreditTransaction $procedure */
        $procedure = $container->get(GetUserCreditTransaction::class);
        $procedure->currentPage = 1;
        $procedure->pageSize = 10;

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('暂无记录');

        $procedure->execute();
    }
}
