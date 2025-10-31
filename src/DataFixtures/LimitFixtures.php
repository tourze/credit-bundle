<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class LimitFixtures extends Fixture implements DependentFixtureInterface
{
    public const LIMIT_TOTAL_OUT = 'credit-limit-total-out';
    public const LIMIT_DAILY_OUT = 'credit-limit-daily-out';
    public const LIMIT_DAILY_IN = 'credit-limit-daily-in';
    public const LIMIT_CREDIT = 'credit-limit-credit';

    public function load(ObjectManager $manager): void
    {
        // 总限制转出
        $totalOutLimit = new Limit();
        $totalOutLimit->setAccount($this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class));
        $totalOutLimit->setType(LimitType::TOTAL_OUT_LIMIT);
        $totalOutLimit->setValue(10000);
        $totalOutLimit->setRemark('Total outbound transfer limit');

        $manager->persist($totalOutLimit);
        $this->addReference(self::LIMIT_TOTAL_OUT, $totalOutLimit);

        // 每日限制转出
        $dailyOutLimit = new Limit();
        $dailyOutLimit->setAccount($this->getReference(AccountFixtures::ACCOUNT_DEMO, Account::class));
        $dailyOutLimit->setType(LimitType::DAILY_OUT_LIMIT);
        $dailyOutLimit->setValue(1000);
        $dailyOutLimit->setRemark('Daily outbound transfer limit');

        $manager->persist($dailyOutLimit);
        $this->addReference(self::LIMIT_DAILY_OUT, $dailyOutLimit);

        // 每日限制转入
        $dailyInLimit = new Limit();
        $dailyInLimit->setAccount($this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class));
        $dailyInLimit->setType(LimitType::DAILY_IN_LIMIT);
        $dailyInLimit->setValue(5000);
        $dailyInLimit->setRemark('Daily inbound transfer limit');

        $manager->persist($dailyInLimit);
        $this->addReference(self::LIMIT_DAILY_IN, $dailyInLimit);

        // 信用额度
        $creditLimit = new Limit();
        $creditLimit->setAccount($this->getReference(AccountFixtures::ACCOUNT_DEMO, Account::class));
        $creditLimit->setType(LimitType::CREDIT_LIMIT);
        $creditLimit->setValue(50000);
        $creditLimit->setRemark('Credit line limit');

        $manager->persist($creditLimit);
        $this->addReference(self::LIMIT_CREDIT, $creditLimit);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
