<?php

namespace CreditBundle\Procedure;

use Carbon\Carbon;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use CreditBundle\Service\TransactionService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPCLockBundle\Procedure\LockableProcedure;
use Tourze\JsonRPCSignBundle\Attribute\CheckSign;
use Tourze\UserIDBundle\Service\UserIdentityService;

#[CheckSign]
#[MethodTag('积分模块')]
#[MethodDoc('创建积分订单')]
#[MethodExpose('ServerCreateCreditTransactionByIdentity')]
#[WithMonologChannel('procedure')]
class ServerCreateCreditTransactionByIdentity extends LockableProcedure
{
    #[MethodParam('身份名')]
    public string $identityType;

    #[MethodParam('身份值')]
    public string $identityValue;

    #[MethodParam('币种')]
    public string $currency;

    #[MethodParam('事件编码')]
    public string $eventNo;

    #[MethodParam('数值')]
    public int $amount;

    #[MethodParam('数值')]
    public string $remark = '';

    #[MethodParam('过期时间')]
    public string $expireTime = '';

    public function __construct(
        private readonly UserIdentityService $userIdentityService,
        private readonly AccountService $accountService,
        private readonly CurrencyService $currencyService,
        private readonly LoggerInterface $logger,
        private readonly TransactionService $transactionService,
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

        if (!$this->amount) {
            throw new ApiException('数值不正确');
        }

        try {
            if ($this->amount > 0) {
                $expireTime = null;
                if ($this->expireTime) {
                    $expireTime = Carbon::parse($this->expireTime);
                }
                $this->transactionService->increase($this->eventNo, $account, $this->amount, $this->remark, $expireTime);
            } else {
                $this->transactionService->decrease($this->eventNo, $account, $this->amount, $this->remark);
            }
        } catch (\Throwable $throwable) {
            $this->logger->error('创建积分订单失败', [
                'error' => $throwable,
            ]);
            if ($throwable instanceof ApiException) {
                throw $throwable;
            }
            throw new ApiException('操作失败');
        }

        return [
            'message' => '操作成功',
        ];
    }

    protected function getLockResource(JsonRpcParams $params): array
    {
        return [
            'ServerCreateCreditTransactionByIdentity' . $params->get('identityType') . $params->get('identityValue') . $params->get('currency'),
        ];
    }
}
