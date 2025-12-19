<?php

declare(strict_types=1);

namespace CreditBundle\Procedure;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Transaction;
use CreditBundle\Param\GetCreditTransactionsByBizUserIdentityParam;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Result\ArrayResult;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag(name: '积分模块')]
#[MethodDoc(summary: '获取指定用户的积分流水（分页）')]
#[MethodExpose(method: 'GetCreditTransactionsByBizUserIdentity')]
class GetCreditTransactionsByBizUserIdentity extends CacheableProcedure
{
    use PaginatorTrait;

    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountRepository $accountRepository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    /**
     * @phpstan-param GetCreditTransactionsByBizUserIdentityParam $param
     */
    public function execute(GetCreditTransactionsByBizUserIdentityParam|RpcParamInterface $param): ArrayResult
    {
        $user = $this->userLoader->loadUserByIdentifier($param->userId);
        if (null === $user) {
            throw new ApiException('暂无记录');
        }
        $account = $this->accountRepository->findOneBy(['user' => $user]);
        if (null === $account) {
            throw new ApiException('暂无记录');
        }

        $qb = $this->transactionRepository->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->setParameter('account', $account)
            ->addOrderBy('a.id', 'DESC')
        ;

        if ('' !== $param->startTime) {
            $qb = $qb->andWhere('a.createTime > :startTime')
                ->setParameter('startTime', CarbonImmutable::parse($param->startTime))
            ;
        }
        if ('' !== $param->endTime) {
            $qb = $qb->andWhere('a.createTime < :endTime')
                ->setParameter('endTime', CarbonImmutable::parse($param->endTime))
            ;
        }

        return new ArrayResult($this->fetchList($qb, function (Transaction $item) {
            $tmp = [
                'id' => $item->getId(),
                'amount' => $item->getAmount(),
                'createTime' => $item->getCreateTime()?->format('Y-m-d H:i:s'),
                'remark' => $item->getRemark(),
            ];

            // 转出
            if ($item->getAmount() < 0) {
                $tmp['outAmount'] = '-' . abs((float) $item->getAmount());
                $tmp['type'] = 'out';
            }

            // 转入
            if ($item->getAmount() > 0) {
                // 当前总积分 = 转入金额 - 消耗情况
                $tmp['inAmount'] = '+' . abs((float) $item->getAmount());
                $tmp['type'] = 'in';
            }

            return $tmp;
        }, null, $param));
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $params = $request->getParams();
        assert(null !== $params, 'JsonRPC params should not be null');

        $key = static::buildParamCacheKey($params);
        $userId = $params->get('userId');
        if (null !== $userId && (is_string($userId) || is_int($userId))) {
            $key .= '-' . $userId;
        }

        return $key;
    }

    public function getCacheDuration(JsonRpcRequest $request): int
    {
        return 60;
    }

    public function getCacheTags(JsonRpcRequest $request): iterable
    {
        return [];
    }
}
