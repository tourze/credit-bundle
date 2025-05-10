<?php

namespace CreditBundle\Tests\Service;

use CreditBundle\Entity\Transaction;
use CreditBundle\Event\IncreasedEvent;
use CreditBundle\Service\CreditIncreaseService;
use CreditBundle\Service\TransactionLimitService;
use CreditBundle\Tests\AbstractTestCase;
use CreditBundle\Tests\TestDataFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\LockServiceBundle\Service\LockService;

class CreditIncreaseServiceTest extends AbstractTestCase
{
    private MockObject&TransactionLimitService $limitService;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&LockService $lockService;
    private MockObject&EventDispatcherInterface $eventDispatcher;
    private CreditIncreaseService $service;
    
    protected function setUp(): void
    {
        $this->limitService = $this->createMock(TransactionLimitService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->lockService = $this->createMock(LockService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        
        $this->service = new CreditIncreaseService(
            $this->limitService,
            $this->entityManager,
            $this->lockService,
            $this->eventDispatcher
        );
    }
    
    /**
     * 测试有效数据增加余额
     */
    public function testIncrease_withValidData_increasesBalance(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $initialBalance = '100.00';
        $initialIncreasedAmount = '200.00';
        $account->setEndingBalance($initialBalance);
        $account->setIncreasedAmount($initialIncreasedAmount);
        
        $amount = 50.00;
        $remark = 'Test increase';
        $expireTime = new DateTime('+30 days');
        
        // 配置锁服务模拟
        $this->lockService->expects($this->once())
            ->method('blockingRun')
            ->with($account)
            ->willReturnCallback(function ($resource, $callback) {
                $callback();
                return null;
            });
        
        // 配置限额服务模拟
        $this->limitService->expects($this->once())
            ->method('checkIncreaseLimit')
            ->with($account, $amount);
        
        // 配置实体管理器刷新模拟
        $this->entityManager->expects($this->once())
            ->method('refresh')
            ->with($account);
        
        // 配置实体管理器持久化模拟
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->logicalOr(
                $this->isInstanceOf(Transaction::class),
                $this->identicalTo($account)
            ));
        
        // 配置事务提交模拟
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        // 配置事件调度模拟
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(IncreasedEvent::class))
            ->willReturnArgument(0);
        
        // 执行测试
        $this->service->increase(
            $eventNo,
            $account,
            $amount,
            $remark,
            $expireTime
        );
        
        // 断言账户状态更新
        $this->assertEquals($initialBalance + $amount, $account->getEndingBalance());
        $this->assertEquals($initialIncreasedAmount + $amount, $account->getIncreasedAmount());
    }
    
    /**
     * 测试增加积分是否保存交易记录
     */
    public function testIncrease_savesTransaction(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test increase';
        $expireTime = new DateTime('+30 days');
        $relationModel = 'TestModel';
        $relationId = '12345';
        $context = ['key' => 'value'];
        
        // 捕获持久化的交易对象
        $capturedTransaction = null;
        
        // 配置锁服务模拟
        $this->lockService->expects($this->once())
            ->method('blockingRun')
            ->willReturnCallback(function ($resource, $callback) {
                $callback();
                return null;
            });
        
        // 配置实体管理器持久化模拟
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedTransaction) {
                if ($entity instanceof Transaction) {
                    $capturedTransaction = $entity;
                }
            });
        
        // 执行测试
        $this->service->increase(
            $eventNo,
            $account,
            $amount,
            $remark,
            $expireTime,
            $relationModel,
            $relationId,
            $context
        );
        
        // 断言交易记录是否正确设置
        $this->assertNotNull($capturedTransaction);
        $this->assertEquals($eventNo, $capturedTransaction->getEventNo());
        $this->assertSame($account, $capturedTransaction->getAccount());
        $this->assertSame($account->getCurrency(), $capturedTransaction->getCurrency());
        $this->assertEquals($amount, $capturedTransaction->getAmount());
        $this->assertEquals($amount, $capturedTransaction->getBalance());
        $this->assertEquals($remark, $capturedTransaction->getRemark());
        $this->assertSame($expireTime, $capturedTransaction->getExpireTime());
        $this->assertEquals($relationModel, $capturedTransaction->getRelationModel());
        $this->assertEquals($relationId, $capturedTransaction->getRelationId());
        $this->assertEquals($context, $capturedTransaction->getContext());
    }
    
    /**
     * 测试超出限额的异常
     */
    public function testIncrease_withLimitExceeded_throwsException(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 1000.00;
        $remark = 'Test increase';
        
        // 设置限额检查抛出异常
        $this->limitService->expects($this->once())
            ->method('checkIncreaseLimit')
            ->with($account, $amount)
            ->willThrowException(new \RuntimeException('Limit exceeded'));
        
        // 期望锁服务和实体管理器不会被调用
        $this->lockService->expects($this->never())->method('blockingRun');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');
        
        // 断言异常
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Limit exceeded');
        
        // 执行测试
        $this->service->increase($eventNo, $account, $amount, $remark);
    }
    
    /**
     * 测试是否触发事件
     */
    public function testIncrease_dispatchesEvent(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test increase';
        $context = ['key' => 'value'];
        
        // 配置锁服务模拟
        $this->lockService->expects($this->once())
            ->method('blockingRun')
            ->willReturnCallback(function ($resource, $callback) {
                $callback();
                return null;
            });
        
        // 捕获分发的事件
        $capturedEvent = null;
        
        // 配置事件调度模拟
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(IncreasedEvent::class))
            ->willReturnCallback(function ($event) use (&$capturedEvent) {
                $capturedEvent = $event;
                return $event;
            });
        
        // 执行测试
        $this->service->increase($eventNo, $account, $amount, $remark, null, null, null, $context);
        
        // 断言事件参数是否正确设置
        $this->assertNotNull($capturedEvent);
        $this->assertSame($account, $capturedEvent->getAccount());
        $this->assertEquals($remark, $capturedEvent->getRemark());
        $this->assertEquals($eventNo, $capturedEvent->getEventNo());
        $this->assertEquals($context, $capturedEvent->getContext());
        $this->assertEquals($amount, $capturedEvent->getAmount());
    }
    
    /**
     * 测试异步增加方法调用增加方法
     */
    public function testAsyncIncrease_callsIncreaseMethod(): void
    {
        // 创建部分模拟的CreditIncreaseService以跟踪increase方法调用
        $service = $this->getMockBuilder(CreditIncreaseService::class)
            ->setConstructorArgs([
                $this->limitService,
                $this->entityManager,
                $this->lockService,
                $this->eventDispatcher
            ])
            ->onlyMethods(['increase'])
            ->getMock();
        
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test async increase';
        $expireTime = new DateTime('+30 days');
        $relationModel = 'TestModel';
        $relationId = '12345';
        $context = ['key' => 'value'];
        
        // 期望increase方法被调用
        $service->expects($this->once())
            ->method('increase')
            ->with(
                $eventNo,
                $account,
                $amount,
                $remark,
                $expireTime,
                $relationModel,
                $relationId,
                $context
            );
        
        // 执行测试
        $service->asyncIncrease(
            $eventNo,
            $account,
            $amount,
            $remark,
            $expireTime,
            $relationModel,
            $relationId,
            $context
        );
    }
    
    /**
     * 测试负数金额自动转为正数
     */
    public function testIncrease_withNegativeAmount_convertsToPositive(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $negativeAmount = -50.00;
        $positiveAmount = 50.00;
        $remark = 'Test negative amount';
        
        // 捕获持久化的交易对象
        $capturedTransaction = null;
        
        // 配置限额服务模拟，验证金额是正数
        $this->limitService->expects($this->once())
            ->method('checkIncreaseLimit')
            ->with($account, $positiveAmount);
        
        // 配置锁服务模拟
        $this->lockService->expects($this->once())
            ->method('blockingRun')
            ->willReturnCallback(function ($resource, $callback) {
                $callback();
                return null;
            });
        
        // 配置实体管理器持久化模拟
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedTransaction) {
                if ($entity instanceof Transaction) {
                    $capturedTransaction = $entity;
                }
            });
        
        // 执行测试
        $this->service->increase($eventNo, $account, $negativeAmount, $remark);
        
        // 断言交易记录中的金额是正数
        $this->assertNotNull($capturedTransaction);
        $this->assertEquals($positiveAmount, $capturedTransaction->getAmount());
    }
} 