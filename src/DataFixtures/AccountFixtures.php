<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class AccountFixtures extends Fixture
{
    public const ACCOUNT_TEST = 'credit-account-test';
    public const ACCOUNT_DEMO = 'credit-account-demo';

    public function load(ObjectManager $manager): void
    {
        // 测试账户
        $testAccount = new Account();
        $testAccount->setName('Test Account');
        $testAccount->setCurrency('CNY');

        $manager->persist($testAccount);
        $this->addReference(self::ACCOUNT_TEST, $testAccount);

        // 演示账户
        $demoAccount = new Account();
        $demoAccount->setName('Demo Account');
        $demoAccount->setCurrency('USD');

        $manager->persist($demoAccount);
        $this->addReference(self::ACCOUNT_DEMO, $demoAccount);

        $manager->flush();
    }
}
