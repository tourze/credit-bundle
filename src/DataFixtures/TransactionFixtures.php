<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public const TRANSACTION_INCREASE = 'credit-transaction-increase';
    public const TRANSACTION_DECREASE = 'credit-transaction-decrease';
    public const TRANSACTION_DEPOSIT = 'credit-transaction-deposit';

    public function load(ObjectManager $manager): void
    {
        // 创建增加积分的交易
        $increaseTransaction = new Transaction();
        $increaseTransaction->setEventNo('EVT-INCREASE-001');
        $increaseTransaction->setAccount($this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class));
        $increaseTransaction->setAmount('100.00');
        $increaseTransaction->setBalance('100.00');
        $increaseTransaction->setCurrency('CNY');
        $increaseTransaction->setRemark('Test increase transaction');

        $manager->persist($increaseTransaction);
        $this->addReference(self::TRANSACTION_INCREASE, $increaseTransaction);

        // 创建减少积分的交易
        $decreaseTransaction = new Transaction();
        $decreaseTransaction->setEventNo('EVT-DECREASE-001');
        $decreaseTransaction->setAccount($this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class));
        $decreaseTransaction->setAmount('-25.50');
        $decreaseTransaction->setBalance('74.50');
        $decreaseTransaction->setCurrency('CNY');
        $decreaseTransaction->setRemark('Test decrease transaction');

        $manager->persist($decreaseTransaction);
        $this->addReference(self::TRANSACTION_DECREASE, $decreaseTransaction);

        // 创建存款交易
        $depositTransaction = new Transaction();
        $depositTransaction->setEventNo('EVT-DEPOSIT-001');
        $depositTransaction->setAccount($this->getReference(AccountFixtures::ACCOUNT_DEMO, Account::class));
        $depositTransaction->setAmount('200.00');
        $depositTransaction->setBalance('200.00');
        $depositTransaction->setCurrency('CNY');
        $depositTransaction->setRemark('Test deposit transaction');

        $manager->persist($depositTransaction);
        $this->addReference(self::TRANSACTION_DEPOSIT, $depositTransaction);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
