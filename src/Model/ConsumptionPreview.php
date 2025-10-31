<?php

declare(strict_types=1);

namespace CreditBundle\Model;

use CreditBundle\Entity\Transaction;

/**
 * 积分消费预览模型
 * 用于替代直接返回数组的方式，提供更好的类型安全
 */
class ConsumptionPreview
{
    /**
     * @param Transaction[] $records     可消费的积分记录
     * @param bool          $needsMerge  是否需要合并
     * @param int           $recordCount 记录总数
     */
    public function __construct(
        private readonly array $records,
        private readonly bool $needsMerge,
        private readonly int $recordCount,
    ) {
    }

    /**
     * @return Transaction[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function needsMerge(): bool
    {
        return $this->needsMerge;
    }

    public function getRecordCount(): int
    {
        return $this->recordCount;
    }
}
