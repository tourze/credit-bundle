<?php

namespace CreditBundle\Model;

use CreditBundle\Entity\Transaction;

/**
 * 积分消费预览模型
 * 用于替代直接返回数组的方式，提供更好的类型安全
 */
class ConsumptionPreview
{
    /**
     * @var Transaction[] 可消费的积分记录
     */
    private array $records;

    /**
     * @var bool 是否需要合并小额积分
     */
    private bool $needsMerge;

    /**
     * @var int 记录总数
     */
    private int $recordCount;

    /**
     * @param Transaction[] $records 可消费的积分记录
     * @param bool $needsMerge 是否需要合并
     * @param int $recordCount 记录总数
     */
    public function __construct(array $records, bool $needsMerge, int $recordCount)
    {
        $this->records = $records;
        $this->needsMerge = $needsMerge;
        $this->recordCount = $recordCount;
    }

    /**
     * @return Transaction[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return bool
     */
    public function needsMerge(): bool
    {
        return $this->needsMerge;
    }

    /**
     * @return int
     */
    public function getRecordCount(): int
    {
        return $this->recordCount;
    }
}
