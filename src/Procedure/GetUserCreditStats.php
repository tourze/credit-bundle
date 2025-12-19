<?php

declare(strict_types=1);

namespace CreditBundle\Procedure;

use Carbon\CarbonImmutable;
use CreditBundle\Param\GetUserCreditStatsParam;
use CreditBundle\Service\AccountService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodTag(name: '积分模块')]
#[MethodDoc(summary: '获取用户的总积分（包含即将过期积分）')]
#[MethodExpose(method: 'GetUserCreditStats')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetUserCreditStats extends BaseProcedure
{
    public function __construct(
        private readonly Security $security,
        private readonly AccountService $accountService,
    ) {
    }

    /**
     * @phpstan-param GetUserCreditStatsParam $param
     */
    public function execute(GetUserCreditStatsParam|RpcParamInterface $param): ArrayResult
    {
        $user = $this->security->getUser();
        if (null === $user) {
            throw new \LogicException('User should be authenticated');
        }

        $account = $this->accountService->getAccountByUser($user, $param->currency);

        $now = CarbonImmutable::now();

        return new ArrayResult([
            'expired' => $this->accountService->getExpireAmount($account),
            'expiring' => $this->accountService->getExpiringAmount($account, $now, $now->addDays(30)),
            'total' => $this->accountService->sumIncreasedAmount($account),
            'valid' => $this->accountService->getValidAmount($account),
        ]);
    }
}
