<?php

namespace CreditBundle\Tests;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Entity\Limit;
use CreditBundle\Entity\Transaction;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use CreditBundle\Enum\LimitType;
use Symfony\Component\Security\Core\User\UserInterface;

class TestDataFactory
{
    /**
     * 创建币种代码字符串
     */
    public static function createCurrency(string $code = 'CNY'): string
    {
        return $code;
    }

    /**
     * 创建Account实体
     */
    public static function createAccount(string $name = 'Test Account', ?string $currency = null, ?UserInterface $user = null): Account
    {
        $account = new Account();
        $account->setName($name);

        if (null === $currency) {
            $currency = self::createCurrency();
        }

        $account->setCurrency($currency);

        if (null !== $user) {
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
        ?\DateTimeInterface $expireTime = null,
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setEventNo($eventNo);

        if (null === $account) {
            $account = self::createAccount();
        }

        $transaction->setAccount($account);
        $transaction->setCurrency($account->getCurrency());
        $transaction->setAmount((string) $amount);
        $transaction->setBalance((string) $amount);
        $transaction->setRemark($remark);

        if (null !== $expireTime) {
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
        ?\DateTimeInterface $endTime = null,
    ): Limit {
        $limit = new Limit();

        if (null === $account) {
            $account = self::createAccount();
        }

        $limit->setAccount($account);
        $limit->setType($type ?? LimitType::DAILY_IN_LIMIT);
        $limit->setValue((int) $value);

        // Limit 实体不包含 startTime 和 endTime 字段

        // 使用反射设置ID
        self::setEntityId($limit, 1);

        return $limit;
    }

    /**
     * 创建AdjustRequest实体
     */
    public static function createAdjustRequest(
        ?Account $account = null,
        string $amount = '100.00',
        ?AdjustRequestType $type = null,
        ?AdjustRequestStatus $status = null,
        ?string $remark = 'Test adjust request',
    ): AdjustRequest {
        $adjustRequest = new AdjustRequest();

        if (null === $account) {
            $account = self::createAccount();
        }

        $adjustRequest->setAccount($account);
        $adjustRequest->setAmount($amount);
        $adjustRequest->setType($type ?? AdjustRequestType::INCREASE);
        $adjustRequest->setStatus($status ?? AdjustRequestStatus::EXAMINE);
        $adjustRequest->setRemark($remark);

        // 使用反射设置ID
        self::setEntityId($adjustRequest, 1);

        return $adjustRequest;
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
