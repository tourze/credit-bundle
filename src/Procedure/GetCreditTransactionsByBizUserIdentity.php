<?php

declare(strict_types=1);

namespace CreditBundle\Procedure;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
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

    #[MethodParam(description: '开始时间')]
    public string $startTime = '';

    #[MethodParam(description: '结束时间')]
    public string $endTime = '';

    #[MethodParam(description: '用户ID')]
    public string $userId = '';

    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountRepository $accountRepository,
        private readonly UserLoaderInterface $userLoader,
    ) {
    }

    public function execute(): array
    {
        $user = $this->userLoader->loadUserByIdentifier($this->userId);
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

        if ('' !== $this->startTime) {
            $qb = $qb->andWhere('a.createTime > :startTime')
                ->setParameter('startTime', CarbonImmutable::parse($this->startTime))
            ;
        }
        if ('' !== $this->endTime) {
            $qb = $qb->andWhere('a.createTime < :endTime')
                ->setParameter('endTime', CarbonImmutable::parse($this->endTime))
            ;
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
        });
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
