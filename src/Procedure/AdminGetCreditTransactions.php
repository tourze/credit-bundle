<?php

namespace CreditBundle\Procedure;

use AppBundle\Repository\BizUserRepository;
use Carbon\Carbon;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\AccountService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CurrencyManageBundle\Service\CurrencyManager;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPCPaginatorBundle\Procedure\PaginatorTrait;

#[MethodTag('积分模块')]
#[MethodDoc('拉取积分列表')]
#[MethodExpose('AdminGetCreditTransactions')]
#[IsGranted('ROLE_OPERATOR')]
class AdminGetCreditTransactions extends BaseProcedure
{
    use PaginatorTrait;

    #[MethodParam('开始时间')]
    public string $startTime = '';

    #[MethodParam('结束时间')]
    public string $endTime = '';

    #[MethodParam('客户ID')]
    public ?string $userId = null;

    #[MethodParam('昵称')]
    public ?string $nickName = null;

    #[MethodParam('手机号码')]
    public ?string $mobile = null;

    #[MethodParam('变更类型： increase / decrease')]
    public ?string $changeType = null;

    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountService $accountService,
        private readonly AccountRepository $accountRepository,
        private readonly CurrencyManager $currencyManager,
        private readonly UserLoaderInterface $userLoader,
        private readonly BizUserRepository $bizUserRepository,
    ) {
    }

    public function execute(): array
    {
        $qb = $this->transactionRepository->createQueryBuilder('a')
            ->addOrderBy('a.id', Criteria::DESC);

        // 用户ID
        if (null !== $this->userId) {
            $bizUsers = $this->userLoader->loadUserByIdentifier($this->userId);
            $this->filterBizUsers($qb, $bizUsers);
        }

        // 用户昵称
        if (null !== $this->nickName) {
            $bizUsers = $this->bizUserRepository->createQueryBuilder('a')
                ->andWhere('(a.username LIKE :value OR a.nickName LIKE :value OR a.identity LIKE :value)')
                ->setParameter('value', '%' . $this->nickName . '%')
                ->getQuery()
                ->getResult();
            $this->filterBizUsers($qb, $bizUsers);
        }

        // 手机号码
        if (null !== $this->mobile) {
            $bizUsers = $this->bizUserRepository->createQueryBuilder('a')
                ->andWhere('a.mobile = :mobile')
                ->setParameter('mobile', $this->mobile)
                ->getQuery()
                ->getResult();
            $this->filterBizUsers($qb, $bizUsers);
        }

        // 时间区间
        if ($this->startTime) {
            $qb = $qb->andWhere('a.createTime > :startTime')
                ->setParameter('startTime', Carbon::parse($this->startTime));
        }
        if ($this->endTime) {
            $qb = $qb->andWhere('a.createTime < :endTime')
                ->setParameter('endTime', Carbon::parse($this->endTime));
        }

        // 变更类型
        if (null !== $this->changeType) {
            if ('increase' === $this->changeType) {
                $qb->andWhere('a.amount > 0');
            }
            if ('decrease' === $this->changeType) {
                $qb->andWhere('a.amount < 0');
            }
        }

        return $this->fetchList($qb, fn (Transaction $item) => $item->retrieveAdminArray());
    }

    private function filterBizUsers(QueryBuilder $qb, array $bizUsers): void
    {
        if (empty($bizUsers)) {
            $qb->andWhere('1=2');

            return;
        }

        $accounts = $this->accountRepository->createQueryBuilder('a')
            ->where('a.user IN (:users)')
            ->setParameter('users', $bizUsers)
            ->getQuery()
            ->getResult();
        if (empty($accounts)) {
            $qb->andWhere('2=3');

            return;
        }

        $qb->andWhere('a.account IN (:accounts)');
        $qb->setParameter('accounts', $accounts);
    }
}
