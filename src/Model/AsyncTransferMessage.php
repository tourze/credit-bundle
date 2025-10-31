<?php

declare(strict_types=1);

namespace CreditBundle\Model;

class AsyncTransferMessage
{
    use RequestAttributeTrait;

    private string $fromAccount;

    private string $toAccount;

    private float $amount;

    private ?string $remark = null;

    public function getFromAccount(): string
    {
        return $this->fromAccount;
    }

    public function setFromAccount(string $fromAccount): void
    {
        $this->fromAccount = $fromAccount;
    }

    public function getToAccount(): string
    {
        return $this->toAccount;
    }

    public function setToAccount(string $toAccount): void
    {
        $this->toAccount = $toAccount;
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
}
