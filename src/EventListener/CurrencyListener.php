<?php

namespace CreditBundle\EventListener;

use CreditBundle\Entity\Currency;
use CreditBundle\Repository\AccountRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Tourze\JsonRPC\Core\Exception\ApiException;

#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Currency::class)]
class CurrencyListener
{
    public function __construct(private readonly AccountRepository $accountRepository)
    {
    }

    public function preRemove(Currency $currency): void
    {
        $c = $this->accountRepository->count([
            'currency' => $currency->getCurrency(),
        ]);
        if ($c > 0) {
            throw new ApiException('该积分已有人使用，不允许删除[1]');
        }
    }
}
