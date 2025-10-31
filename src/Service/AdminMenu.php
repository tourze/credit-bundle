<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Entity\ConsumeLog;
use CreditBundle\Entity\Limit;
use CreditBundle\Entity\Transaction;
use CreditBundle\Entity\TransferErrorLog;
use CreditBundle\Entity\TransferLog;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        $creditCenter = $item->getChild('积分中心');
        if (null === $creditCenter) {
            $creditCenter = $item->addChild('积分中心');
        }

        // 账户管理
        $creditCenter->addChild('交易账号')->setUri($this->linkGenerator->getCurdListPage(Account::class));

        // 交易记录
        $creditCenter->addChild('交易流水')->setUri($this->linkGenerator->getCurdListPage(Transaction::class));
        $creditCenter->addChild('积分流水')->setUri($this->linkGenerator->getCurdListPage(TransferLog::class));

        // 管理功能
        $creditCenter->addChild('积分调整请求')->setUri($this->linkGenerator->getCurdListPage(AdjustRequest::class));
        $creditCenter->addChild('配额限制')->setUri($this->linkGenerator->getCurdListPage(Limit::class));

        // 明细记录
        $creditCenter->addChild('积分消耗明细')->setUri($this->linkGenerator->getCurdListPage(ConsumeLog::class));

        // 错误日志
        $creditCenter->addChild('转账错误日志')->setUri($this->linkGenerator->getCurdListPage(TransferErrorLog::class));
    }
}
