<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\TransferErrorLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class TransferErrorLogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $transferErrorLog1 = new TransferErrorLog();
        $transferErrorLog1->setFromAccountId('10001');
        $transferErrorLog1->setFromAccountName('Test From Account 1');
        $transferErrorLog1->setToAccountId('10002');
        $transferErrorLog1->setToAccountName('Test To Account 1');
        $transferErrorLog1->setCurrency('CNY');
        $transferErrorLog1->setAmount(100.50);
        $transferErrorLog1->setException('Transfer failed: Insufficient balance');
        $transferErrorLog1->setContext(['operation_id' => 'op_001', 'user_id' => 101]);

        $transferErrorLog2 = new TransferErrorLog();
        $transferErrorLog2->setFromAccountId('10003');
        $transferErrorLog2->setFromAccountName('Test From Account 2');
        $transferErrorLog2->setToAccountId('10004');
        $transferErrorLog2->setToAccountName('Test To Account 2');
        $transferErrorLog2->setCurrency('USD');
        $transferErrorLog2->setAmount(250.75);
        $transferErrorLog2->setException('Transfer failed: Account not found');
        $transferErrorLog2->setContext(['operation_id' => 'op_002', 'user_id' => 102]);

        $transferErrorLog3 = new TransferErrorLog();
        $transferErrorLog3->setFromAccountId('10005');
        $transferErrorLog3->setFromAccountName('Test From Account 3');
        $transferErrorLog3->setToAccountId('10006');
        $transferErrorLog3->setToAccountName('Test To Account 3');
        $transferErrorLog3->setCurrency('CNY');
        $transferErrorLog3->setAmount(null);
        $transferErrorLog3->setException('Transfer failed: Network timeout');
        $transferErrorLog3->setContext(['operation_id' => 'op_003', 'user_id' => 103]);

        $manager->persist($transferErrorLog1);
        $manager->persist($transferErrorLog2);
        $manager->persist($transferErrorLog3);

        $manager->flush();
    }
}
