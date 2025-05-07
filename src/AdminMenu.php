<?php

namespace CreditBundle;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Currency;
use CreditBundle\Entity\TransferLog;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

#[MenuProvider]
class AdminMenu
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (!$item->getChild('积分中心')) {
            $item->addChild('积分中心');
        }

        $item->getChild('积分中心')->addChild('积分流水')->setUri($this->linkGenerator->getCurdListPage(TransferLog::class));
        $item->getChild('积分中心')->addChild('积分管理')->setUri($this->linkGenerator->getCurdListPage(Currency::class));
        $item->getChild('积分中心')->addChild('交易账号')->setUri($this->linkGenerator->getCurdListPage(Account::class));
    }
}
