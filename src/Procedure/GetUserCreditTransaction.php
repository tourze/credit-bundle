<?php

namespace CreditBundle\Procedure;

use Carbon\Carbon;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\AccountService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CurrencyManageBundle\Service\CurrencyManager;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag('积分模块')]
#[MethodDoc('获取用户的积分记录，总积分')]
#[MethodExpose('GetUserCreditTransaction')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class GetUserCreditTransaction extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam('开始时间')]
    public string $startTime = '';

    #[MethodParam('结束时间')]
    public string $endTime = '';

    public function __construct(
        private readonly Security $security,
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountService $accountService,
        private readonly AccountRepository $accountRepository,
        private readonly CurrencyManager $currencyManager,
    ) {
    }

    public function execute(): array
    {
        $account = $this->accountRepository->findOneBy(['user' => $this->security->getUser()]);
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

        // 转化数据，计算总积分
        $now = Carbon::now();
        $totalCredit = $this->accountService->getValidAmount($account);  // 当前总积分
        $expiringCredit = $this->accountService->getExpiringAmount($account, $now, $now->addDays(30)); // 即将过期积分
        $result = $this->fetchList($qb, function (Transaction $item) {
            // todo 后面改成event处理
            if ('数据迁移20241216' == $item->getRemark()) {
                return null;
            }
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

        if (!$result) {
            throw new ApiException('没有数据');
        }
        $result['total'] = $totalCredit;
        $result['expiring'] = $expiringCredit;

        return $result;
    }
}
