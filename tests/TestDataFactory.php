<?php

namespace CreditBundle\Tests;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Currency;
use CreditBundle\Entity\Limit;
use CreditBundle\Entity\Transaction;
use CreditBundle\Enum\LimitType;
use Symfony\Component\Security\Core\User\UserInterface;

class TestDataFactory
{
    /**
     * 创建Currency实体
     */
    public static function createCurrency(string $code = 'CNY', string $name = '人民币', bool $main = true, bool $valid = true): Currency
    {
        $currency = new Currency();
        $currency->setCurrency($code);
        $currency->setName($name);
        $currency->setMain($main);
        $currency->setValid($valid);
        
        // 使用反射设置ID
        self::setEntityId($currency, 1);
        
        return $currency;
    }
    
    /**
     * 创建Account实体
     */
    public static function createAccount(string $name = 'Test Account', ?Currency $currency = null, ?UserInterface $user = null): Account
    {
        $account = new Account();
        $account->setName($name);
        
        if ($currency === null) {
            $currency = self::createCurrency();
        }
        
        $account->setCurrency($currency);
        
        if ($user !== null) {
            $account->setUser($user);
        }
        
        // 使用反射设置ID
        self::setEntityId($account, 1);
        
        return $account;
    }
    
    /**
     * 创建Transaction实体
     */
    public static function createTransaction(
        string $eventNo = 'TEST-123',
        ?Account $account = null,
        float $amount = 100.00,
        ?string $remark = 'Test transaction',
        ?\DateTimeInterface $expireTime = null
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setEventNo($eventNo);
        
        if ($account === null) {
            $account = self::createAccount();
        }
        
        $transaction->setAccount($account);
        $transaction->setCurrency($account->getCurrency());
        $transaction->setAmount($amount);
        $transaction->setBalance($amount);
        $transaction->setRemark($remark);
        
        if ($expireTime !== null) {
            $transaction->setExpireTime($expireTime);
        }
        
        // 使用反射设置ID
        self::setEntityId($transaction, 1);
        
        return $transaction;
    }
    
    /**
     * 创建Limit实体
     */
    public static function createLimit(
        ?Account $account = null,
        ?LimitType $type = null,
        float $value = 1000.00,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null
    ): Limit {
        $limit = new Limit();
        
        if ($account === null) {
            $account = self::createAccount();
        }
        
        $limit->setAccount($account);
        $limit->setType($type ?? LimitType::DAILY_IN_LIMIT);
        $limit->setValue((int)$value);
        
        if ($startTime !== null) {
            $limit->setStartTime($startTime);
        }
        
        if ($endTime !== null) {
            $limit->setEndTime($endTime);
        }
        
        // 使用反射设置ID
        self::setEntityId($limit, 1);
        
        return $limit;
    }
    
    /**
     * 使用反射设置实体ID
     */
    private static function setEntityId(object $entity, int $id): void
    {
        try {
            $reflection = new \ReflectionClass($entity);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($entity, $id);
        } catch (\ReflectionException $e) {
            // 忽略反射异常
        }
    }
} 