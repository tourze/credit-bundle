<?php

declare(strict_types=1);

namespace CreditBundle\Event;

use CreditBundle\Entity\Account;
use Symfony\Contracts\EventDispatcher\Event;

final class IncreasedEvent extends Event
{
    private Account $account;

    private ?string $remark = null;

    private ?float $amount = null;

    private string $eventNo;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $context = null;

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

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }
}
