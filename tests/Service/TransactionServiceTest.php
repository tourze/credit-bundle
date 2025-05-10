<?php

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\CreditDecreaseService;
use CreditBundle\Service\CreditIncreaseService;
use CreditBundle\Service\TransactionService;
use CreditBundle\Tests\AbstractTestCase;
use CreditBundle\Tests\TestDataFactory;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\SnowflakeBundle\Service\Snowflake;

class TransactionServiceTest extends AbstractTestCase
{
    private MockObject&Snowflake $snowflake;
    private MockObject&CreditIncreaseService $increaseService;
    private MockObject&CreditDecreaseService $decreaseService;
    private TransactionService $service;
    
    protected function setUp(): void
    {
        $this->snowflake = $this->createMock(Snowflake::class);
        $this->increaseService = $this->createMock(CreditIncreaseService::class);
        $this->decreaseService = $this->createMock(CreditDecreaseService::class);
        
        $this->service = new TransactionService(
            $this->snowflake,
            $this->increaseService,
            $this->decreaseService
        );
    }
    
    /**
     * 测试转账调用减少和增加方法
     */
    public function testTransfer_callsDecreaseAndIncrease(): void
    {
        // 准备测试数据
        $user = $this->createMockUser();
        $fromAccount = TestDataFactory::createAccount('From Account');
        $fromAccount->setUser($user);
        $toAccount = TestDataFactory::createAccount('To Account');
        $toAccount->setUser($user);
        $amount = 100.00;
        $remark = 'Test transfer';
        $context = ['key' => 'value'];
        $snowflakeId = '123456789';
        $eventNo = 'S' . $snowflakeId;
        
        // 设置Snowflake模拟
        $this->snowflake->expects($this->once())
            ->method('id')
            ->willReturn($snowflakeId);
        
        // 设置从账户减少积分的期望
        $this->decreaseService->expects($this->once())
            ->method('decrease')
            ->with(
                $eventNo,
                $fromAccount,
                $amount,
                $remark,
                null,
                null,
                $context,
                false
            );
        
        // 设置向账户增加积分的期望
        $this->increaseService->expects($this->once())
            ->method('increase')
            ->with(
                $eventNo,
                $toAccount,
                $amount,
                $remark,
                null,
                null,
                null,
                $context
            );
        
        // 执行测试
        $result = $this->service->transfer($fromAccount, $toAccount, $amount, $remark, $context);
        
        // 断言
        $this->assertEquals($eventNo, $result);
    }
    
    /**
     * 测试当from账户无用户时不调用decrease
     */
    public function testTransfer_withFromAccountWithoutUser_doesNotCallDecrease(): void
    {
        // 准备测试数据
        $fromAccount = TestDataFactory::createAccount('From Account');
        $fromAccount->setUser(null);
        $toAccount = TestDataFactory::createAccount('To Account');
        $toAccount->setUser($this->createMockUser());
        $amount = 100.00;
        $remark = 'Test transfer';
        $context = ['key' => 'value'];
        $snowflakeId = '123456789';
        $eventNo = 'S' . $snowflakeId;
        
        // 设置Snowflake模拟
        $this->snowflake->expects($this->once())
            ->method('id')
            ->willReturn($snowflakeId);
        
        // 期望不调用decrease方法
        $this->decreaseService->expects($this->never())
            ->method('decrease');
        
        // 设置向账户增加积分的期望
        $this->increaseService->expects($this->once())
            ->method('increase');
        
        // 执行测试
        $result = $this->service->transfer($fromAccount, $toAccount, $amount, $remark, $context);
        
        // 断言
        $this->assertEquals($eventNo, $result);
    }
    
    /**
     * 测试当to账户无用户时不调用increase
     */
    public function testTransfer_withToAccountWithoutUser_doesNotCallIncrease(): void
    {
        // 准备测试数据
        $fromAccount = TestDataFactory::createAccount('From Account');
        $fromAccount->setUser($this->createMockUser());
        $toAccount = TestDataFactory::createAccount('To Account');
        $toAccount->setUser(null);
        $amount = 100.00;
        $remark = 'Test transfer';
        $context = ['key' => 'value'];
        $snowflakeId = '123456789';
        $eventNo = 'S' . $snowflakeId;
        
        // 设置Snowflake模拟
        $this->snowflake->expects($this->once())
            ->method('id')
            ->willReturn($snowflakeId);
        
        // 设置从账户减少积分的期望
        $this->decreaseService->expects($this->once())
            ->method('decrease');
        
        // 期望不调用increase方法
        $this->increaseService->expects($this->never())
            ->method('increase');
        
        // 执行测试
        $result = $this->service->transfer($fromAccount, $toAccount, $amount, $remark, $context);
        
        // 断言
        $this->assertEquals($eventNo, $result);
    }
    
    /**
     * 测试增加方法调用增加服务
     */
    public function testIncrease_callsIncreaseService(): void
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
        
        // 设置增加积分服务的期望
        $this->increaseService->expects($this->once())
            ->method('increase')
            ->with(
                $this->equalTo($eventNo),
                $this->identicalTo($account),
                $this->equalTo($amount),
                $this->equalTo($remark),
                $this->identicalTo($expireTime),
                $this->equalTo($relationModel),
                $this->equalTo($relationId),
                $this->equalTo($context)
            );
        
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
    }
    
    /**
     * 测试减少方法调用减少服务
     */
    public function testDecrease_callsDecreaseService(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test decrease';
        $relationModel = 'TestModel';
        $relationId = '12345';
        $context = ['key' => 'value'];
        $isExpired = true;
        
        // 设置减少积分服务的期望
        $this->decreaseService->expects($this->once())
            ->method('decrease')
            ->with(
                $this->equalTo($eventNo),
                $this->identicalTo($account),
                $this->equalTo($amount),
                $this->equalTo($remark),
                $this->equalTo($relationModel),
                $this->equalTo($relationId),
                $this->equalTo($context),
                $this->equalTo($isExpired)
            );
        
        // 执行测试
        $this->service->decrease(
            $eventNo,
            $account,
            $amount,
            $remark,
            $relationModel,
            $relationId,
            $context,
            $isExpired
        );
    }
    
    /**
     * 测试回滚方法调用回滚服务
     */
    public function testRollback_callsRollbackService(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test rollback';
        $relationModel = 'TestModel';
        $relationId = '12345';
        $context = ['key' => 'value'];
        $isExpired = false;
        
        // 设置回滚积分服务的期望
        $this->decreaseService->expects($this->once())
            ->method('rollback')
            ->with(
                $this->equalTo($eventNo),
                $this->identicalTo($account),
                $this->equalTo($amount),
                $this->equalTo($remark),
                $this->equalTo($relationModel),
                $this->equalTo($relationId),
                $this->equalTo($context),
                $this->equalTo($isExpired)
            );
        
        // 执行测试
        $this->service->rollback(
            $eventNo,
            $account,
            $amount,
            $remark,
            $relationModel,
            $relationId,
            $context,
            $isExpired
        );
    }
    
    /**
     * 测试异步增加方法调用增加服务
     */
    public function testAsyncIncrease_callsAsyncIncreaseService(): void
    {
        // 准备测试数据
        $eventNo = 'TEST-123';
        $account = TestDataFactory::createAccount();
        $amount = 50.00;
        $remark = 'Test async increase';
        $expireTime = new DateTime('+30 days');
        $relationModel = 'TestModel';
        $relationId = '12345';
        $context = ['key' => 'value'];
        
        // 设置异步增加积分服务的期望
        $this->increaseService->expects($this->once())
            ->method('asyncIncrease')
            ->with(
                $this->equalTo($eventNo),
                $this->identicalTo($account),
                $this->equalTo($amount),
                $this->equalTo($remark),
                $this->identicalTo($expireTime),
                $this->equalTo($relationModel),
                $this->equalTo($relationId),
                $this->equalTo($context)
            );
        
        // 执行测试
        $this->service->asyncIncrease(
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
} 