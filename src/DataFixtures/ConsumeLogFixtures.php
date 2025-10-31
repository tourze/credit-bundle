<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\ConsumeLog;
use CreditBundle\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class ConsumeLogFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONSUME_LOG_TEST = 'credit-consume-log-test';
    public const CONSUME_LOG_DEMO = 'credit-consume-log-demo';

    public function load(ObjectManager $manager): void
    {
        // 创建测试用的消耗日志
        $consumeLog1 = new ConsumeLog();
        $consumeLog1->setCostTransaction($this->getReference(TransactionFixtures::TRANSACTION_INCREASE, Transaction::class));
        $consumeLog1->setConsumeTransaction($this->getReference(TransactionFixtures::TRANSACTION_DECREASE, Transaction::class));
        $consumeLog1->setAmount('25.50');

        $manager->persist($consumeLog1);
        $this->addReference(self::CONSUME_LOG_TEST, $consumeLog1);

        // 创建另一个测试消耗日志
        $consumeLog2 = new ConsumeLog();
        $consumeLog2->setCostTransaction($this->getReference(TransactionFixtures::TRANSACTION_DEPOSIT, Transaction::class));
        $consumeLog2->setConsumeTransaction($this->getReference(TransactionFixtures::TRANSACTION_DECREASE, Transaction::class));
        $consumeLog2->setAmount('12.00');

        $manager->persist($consumeLog2);
        $this->addReference(self::CONSUME_LOG_DEMO, $consumeLog2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TransactionFixtures::class,
        ];
    }
}
