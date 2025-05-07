<?php

namespace CreditBundle\Event;

use CreditBundle\Entity\Account;
use Symfony\Contracts\EventDispatcher\Event;

class DecreasedEvent extends Event
{
    private Account $account;

    private ?string $remark = null;

    private ?float $amount = null;

    private string $eventNo;

    private ?array $context = null;

    private bool $localExecute = true;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getEventNo(): string
    {
        return $this->eventNo;
    }

    public function setEventNo(string $eventNo): void
    {
        $this->eventNo = $eventNo;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function isLocalExecute(): bool
    {
        return $this->localExecute;
    }

    public function setLocalExecute(bool $localExecute): void
    {
        $this->localExecute = $localExecute;
    }
}
