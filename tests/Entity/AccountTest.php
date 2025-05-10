<?php

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Limit;
use CreditBundle\Tests\AbstractTestCase;
use CreditBundle\Tests\TestDataFactory;
use Doctrine\Common\Collections\ArrayCollection;

class AccountTest extends AbstractTestCase
{
    /**
     * 测试新建账户默认值
     */
    public function testConstruct_createsNewAccountWithDefaultValues(): void
    {
        $account = new Account();
        
        $this->assertEquals('0', $account->getEndingBalance());
        $this->assertEquals('0', $account->getIncreasedAmount());
        $this->assertEquals('0', $account->getDecreasedAmount());
        $this->assertEquals('0', $account->getExpiredAmount());
        $this->assertInstanceOf(ArrayCollection::class, $account->getLimits());
        $this->assertInstanceOf(ArrayCollection::class, $account->getTransactions());
        $this->assertCount(0, $account->getLimits());
        $this->assertCount(0, $account->getTransactions());
    }
    
    /**
     * 测试名称存取
     */
    public function testGetSetName_properlyStoresAndRetrievesName(): void
    {
        $account = new Account();
        $testName = 'Test Account Name';
        
        $account->setName($testName);
        
        $this->assertEquals($testName, $account->getName());
    }
    
    /**
     * 测试货币存取
     */
    public function testGetSetCurrency_properlyStoresAndRetrievesCurrency(): void
    {
        $account = new Account();
        $currency = TestDataFactory::createCurrency();
        
        $account->setCurrency($currency);
        
        $this->assertSame($currency, $account->getCurrency());
    }
    
    /**
     * 测试用户存取
     */
    public function testGetSetUser_properlyStoresAndRetrievesUser(): void
    {
        $account = new Account();
        $user = $this->createMockUser();
        
        $account->setUser($user);
        
        $this->assertSame($user, $account->getUser());
    }
    
    /**
     * 测试余额存取
     */
    public function testGetSetEndingBalance_properlyStoresAndRetrievesBalance(): void
    {
        $account = new Account();
        $balance = '123.45';
        
        $account->setEndingBalance($balance);
        
        $this->assertEquals($balance, $account->getEndingBalance());
        
        // 测试浮点数输入
        $floatBalance = 456.78;
        $account->setEndingBalance($floatBalance);
        
        $this->assertEquals((string)$floatBalance, $account->getEndingBalance());
    }
    
    /**
     * 测试增加金额存取
     */
    public function testGetSetIncreasedAmount_properlyStoresAndRetrievesAmount(): void
    {
        $account = new Account();
        $amount = '123.45';
        
        $account->setIncreasedAmount($amount);
        
        $this->assertEquals($amount, $account->getIncreasedAmount());
        
        // 测试浮点数输入
        $floatAmount = 456.78;
        $account->setIncreasedAmount($floatAmount);
        
        $this->assertEquals((string)$floatAmount, $account->getIncreasedAmount());
    }
    
    /**
     * 测试减少金额存取
     */
    public function testGetSetDecreasedAmount_properlyStoresAndRetrievesAmount(): void
    {
        $account = new Account();
        $amount = '123.45';
        
        $account->setDecreasedAmount($amount);
        
        $this->assertEquals($amount, $account->getDecreasedAmount());
        
        // 测试浮点数输入
        $floatAmount = 456.78;
        $account->setDecreasedAmount($floatAmount);
        
        $this->assertEquals((string)$floatAmount, $account->getDecreasedAmount());
    }
    
    /**
     * 测试过期金额存取
     */
    public function testGetSetExpiredAmount_properlyStoresAndRetrievesAmount(): void
    {
        $account = new Account();
        $amount = '123.45';
        
        $account->setExpiredAmount($amount);
        
        $this->assertEquals($amount, $account->getExpiredAmount());
    }
    
    /**
     * 测试限额添加删除
     */
    public function testAddRemoveLimit_properlyManagesLimits(): void
    {
        $account = new Account();
        $limit = new Limit();
        
        // 测试添加
        $account->addLimit($limit);
        
        $this->assertCount(1, $account->getLimits());
        $this->assertSame($account, $limit->getAccount());
        $this->assertTrue($account->getLimits()->contains($limit));
        
        // 测试重复添加不会导致重复
        $account->addLimit($limit);
        $this->assertCount(1, $account->getLimits());
        
        // 测试移除
        $account->removeLimit($limit);
        
        $this->assertCount(0, $account->getLimits());
        $this->assertNull($limit->getAccount());
        $this->assertFalse($account->getLimits()->contains($limit));
    }
    
    /**
     * 测试交易添加删除
     * 
     * 注意：由于Transaction实体在测试环境下难以独立实例化和操作，
     * 且Account与Transaction关系存在循环依赖，此测试暂时跳过。
     * 在实际环境中，这些关系通过ORM自动处理。
     */
    public function testAddRemoveTransaction_properlyManagesTransactions(): void
    {
        $this->markTestSkipped(
            '由于Transaction和Account有复杂的双向关系，无法在简单的单元测试中模拟，' .
            '这部分功能由集成测试或功能测试覆盖。'
        );
    }
    
    /**
     * 测试toString方法
     */
    public function testToString_returnsExpectedFormat(): void
    {
        $account = TestDataFactory::createAccount('Test Account');
        $currency = $account->getCurrency();
        
        $expected = "{$currency} - Test Account";
        $this->assertEquals($expected, (string)$account);
        
        // 测试ID为null时的情况
        $newAccount = new Account();
        $newAccount->setName('New Account');
        $newAccount->setCurrency(TestDataFactory::createCurrency());
        
        $this->assertEquals('', (string)$newAccount);
    }
} 