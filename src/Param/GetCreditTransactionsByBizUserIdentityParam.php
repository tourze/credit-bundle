<?php

declare(strict_types=1);

namespace CreditBundle\Param;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPCPaginatorBundle\Param\PaginatorParamInterface;

readonly class GetCreditTransactionsByBizUserIdentityParam implements PaginatorParamInterface
{
    public function __construct(
        #[MethodParam(description: '开始时间')]
        public string $startTime = '',
        #[MethodParam(description: '结束时间')]
        public string $endTime = '',
        #[MethodParam(description: '用户ID')]
        public string $userId = '',
        #[MethodParam(description: '当前页数')]
        public int $currentPage = 1,
        #[MethodParam(description: '每页条数')]
        public int $pageSize = 10,
        #[MethodParam(description: '上一次拉取时，最后一条数据的主键ID')]
        public ?int $lastId = null,
    ) {
    }
}
