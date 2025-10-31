<?php

declare(strict_types=1);

namespace CreditBundle\DataFixtures;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class AdjustRequestFixtures extends Fixture implements DependentFixtureInterface
{
    public const ADJUST_REQUEST_INCREASE = 'credit-adjust-request-increase';
    public const ADJUST_REQUEST_DECREASE = 'credit-adjust-request-decrease';

    public function load(ObjectManager $manager): void
    {
        // 增加积分的调整请求
        $increaseRequest = new AdjustRequest();
        $increaseRequest->setAccount($this->getReference(AccountFixtures::ACCOUNT_TEST, Account::class));
        $increaseRequest->setAmount('100.50');
        $increaseRequest->setType(AdjustRequestType::INCREASE);
        $increaseRequest->setStatus(AdjustRequestStatus::EXAMINE);
        $increaseRequest->setRemark('Test increase request for bonus points');

        $manager->persist($increaseRequest);
        $this->addReference(self::ADJUST_REQUEST_INCREASE, $increaseRequest);

        // 减少积分的调整请求
        $decreaseRequest = new AdjustRequest();
        $decreaseRequest->setAccount($this->getReference(AccountFixtures::ACCOUNT_DEMO, Account::class));
        $decreaseRequest->setAmount('50.00');
        $decreaseRequest->setType(AdjustRequestType::DECREASE);
        $decreaseRequest->setStatus(AdjustRequestStatus::PASS);
        $decreaseRequest->setRemark('Test decrease request for penalty');

        $manager->persist($decreaseRequest);
        $this->addReference(self::ADJUST_REQUEST_DECREASE, $decreaseRequest);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccountFixtures::class,
        ];
    }
}
