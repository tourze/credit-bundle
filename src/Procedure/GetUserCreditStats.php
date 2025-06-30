<?php

namespace CreditBundle\Procedure;

use Carbon\CarbonImmutable;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodTag(name: '积分模块')]
#[MethodDoc(summary: '获取用户的总积分（包含即将过期积分）')]
#[MethodExpose(method: 'GetUserCreditStats')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetUserCreditStats extends BaseProcedure
{
    #[MethodParam(description: '要查询的币种')]
    public string $currency;

    public function __construct(
        private readonly Security $security,
        private readonly AccountService $accountService,
        private readonly CurrencyService $currencyService,
    ) {
    }

    public function execute(): array
    {
        $currency = $this->currencyService->getCurrencyByCode($this->currency);
        $account = $this->accountService->getAccountByUser($this->security->getUser(), $currency);

        $now = CarbonImmutable::now();

        return [
            'expired' => $this->accountService->getExpireAmount($account),
            'expiring' => $this->accountService->getExpiringAmount($account, $now, $now->addDays(30)),
            'total' => $this->accountService->sumIncreasedAmount($account),
            'valid' => $this->accountService->getValidAmount($account),
        ];
    }
}
