<?php

namespace CreditBundle\Model;

/**
 * 交易参与方
 */
class TransactionParticipant
{
    /**
     * @var string 账号名
     */
    private string $name;

    /**
     * @var float 变动金额
     */
    private float $amount;

    /**
     * @var string|null 备注
     */
    private ?string $remark = null;

    /**
     * @var \DateTimeInterface 当前这笔收入的过期情况
     */
    private \DateTimeInterface $expireTime;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getExpireTime(): \DateTimeInterface
    {
        return $this->expireTime;
    }

    public function setExpireTime(\DateTimeInterface $expireTime): void
    {
        $this->expireTime = $expireTime;
    }
}
