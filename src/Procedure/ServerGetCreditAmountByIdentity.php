<?php

namespace CreditBundle\Procedure;

use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCSignBundle\Attribute\CheckSign;
use Tourze\UserIDBundle\Service\UserIdentityService;

#[CheckSign]
#[MethodTag('积分模块')]
#[MethodDoc('根据身份信息读取积分信息')]
#[MethodExpose('ServerGetCreditAmountByIdentity')]
class ServerGetCreditAmountByIdentity extends BaseProcedure
{
    #[MethodParam('身份名')]
    public string $identityType;

    #[MethodParam('身份值')]
    public string $identityValue;

    #[MethodParam('币种')]
    public string $currency;

    public function __construct(
        private readonly UserIdentityService $userIdentityService,
        private readonly AccountService $accountService,
        private readonly CurrencyService $currencyService,
    ) {
    }

    public function execute(): array
    {
        $bizUser = $this->userIdentityService->findByType($this->identityType, $this->identityValue)?->getBelongUser();
        if (!$bizUser) {
            throw new ApiException('找不到指定身份关联的客户');
        }
        $currency = $this->currencyService->getCurrencyByCode($this->currency);
        if (!$currency) {
            throw new ApiException('币种不存在');
        }
        $account = $this->accountService->getAccountByUser($bizUser, $currency);

        $validAmount = $this->accountService->getValidAmount($account);
        $expireAmountAmount = $this->accountService->getExpireAmount($account);

        return [
            'message' => '操作成功',
            'validAmount' => $validAmount,
            'expireAmountAmount' => $expireAmountAmount,
        ];
    }
}
