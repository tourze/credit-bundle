<?php

namespace CreditBundle\Procedure;

use Carbon\Carbon;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\AccountService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Tourze\CurrencyManageBundle\Service\CurrencyManager;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag('积分模块')]
#[MethodDoc('获取指定用户的积分流水（分页）')]
#[MethodExpose('GetCreditTransactionsByBizUserId')]
class GetCreditTransactionsByBizUserId extends CacheableProcedure
{
    use PaginatorTrait;

    #[MethodParam('开始时间')]
    public string $startTime = '';

    #[MethodParam('结束时间')]
    public string $endTime = '';

    #[MethodParam('用户ID')]
    public string $userId = '';

    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountService $accountService,
        private readonly AccountRepository $accountRepository,
        private readonly CurrencyManager $currencyManager,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
        if (empty($user)) {
            throw new ApiException('暂无记录');
        }
        $account = $this->accountRepository->findOneBy(['user' => $user]);
        if (empty($account)) {
            throw new ApiException('暂无记录');
        }

        $qb = $this->transactionRepository->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->setParameter('account', $account)
            ->addOrderBy('a.id', Criteria::DESC);

        if ($this->startTime) {
            $qb = $qb->andWhere('a.createTime > :startTime')
                ->setParameter('startTime', Carbon::parse($this->startTime));
        }
        if ($this->endTime) {
            $qb = $qb->andWhere('a.createTime < :endTime')
                ->setParameter('endTime', Carbon::parse($this->endTime));
        }

        return $this->fetchList($qb, function (Transaction $item) {
            $tmp = [
                'id' => $item->getId(),
                'amount' => $item->getAmount(),
                'createTime' => $item->getCreateTime()?->format('Y-m-d H:i:s'),
                'remark' => $item->getRemark(),
            ];

            // 转出
            if ($item->getAmount() < 0) {
                $tmp['outAmount'] = '-' . abs($this->currencyManager->getPriceNumber($item->getAmount()));
                $tmp['type'] = 'out';
            }

            // 转入
            if ($item->getAmount() > 0) {
                // 当前总积分 = 转入金额 - 消耗情况
                $tmp['inAmount'] = '+' . abs($this->currencyManager->getPriceNumber($item->getAmount()));
                $tmp['type'] = 'in';
            }

            return $tmp;
        });
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $key = static::buildParamCacheKey($request->getParams());
        if ($request->getParams()->get('userId')) {
            $key .= '-' . $request->getParams()->get('userId');
        }

        return $key;
    }

    public function getCacheDuration(JsonRpcRequest $request): int
    {
        return 60;
    }

    public function getCacheTags(JsonRpcRequest $request): iterable
    {
        yield null;
    }
}
