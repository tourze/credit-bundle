<?php

declare(strict_types=1);

namespace CreditBundle\Param;

use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class GetUserCreditStatsParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '要查询的币种')]
        public string $currency,
    ) {
    }
}
