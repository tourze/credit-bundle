<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\TransferLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class TransferLogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Account $testAccount */
        $testAccount = $this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class);

        /** @var Account $demoAccount */
        $demoAccount = $this->getReference(AccountFixtures::ACCOUNT_DEMO, Account::class);

        // 转账记录 1: 从测试账户到演示账户
        $transferLog1 = new TransferLog();
        $transferLog1->setCurrency('CNY');
        $transferLog1->setOutAccount($testAccount);
        $transferLog1->setOutAmount('100.50');
        $transferLog1->setInAccount($demoAccount);
        $transferLog1->setInAmount('100.50');
        $transferLog1->setRemark('Test transfer from test account to demo account');

        // 转账记录 2: 从演示账户到测试账户
        $transferLog2 = new TransferLog();
        $transferLog2->setCurrency('USD');
        $transferLog2->setOutAccount($demoAccount);
        $transferLog2->setOutAmount('50.25');
        $transferLog2->setInAccount($testAccount);
        $transferLog2->setInAmount('50.25');
        $transferLog2->setRemark('Test transfer from demo account to test account');
        $transferLog2->setExpireTime(new \DateTimeImmutable('+30 days'));

        // 转账记录 3: 带关联信息的转账
        $transferLog3 = new TransferLog();
        $transferLog3->setCurrency('CNY');
        $transferLog3->setOutAccount($testAccount);
        $transferLog3->setOutAmount('200.00');
        $transferLog3->setInAccount($testAccount); // 同一账户内部转账
        $transferLog3->setInAmount('200.00');
        $transferLog3->setRemark('Internal transfer for testing');
        $transferLog3->setRelationId('test_relation_001');
        $transferLog3->setRelationModel('TestModel');

        $manager->persist($transferLog1);
        $manager->persist($transferLog2);
        $manager->persist($transferLog3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
