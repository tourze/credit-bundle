<?php

namespace CreditBundle\Event;

use CreditBundle\Entity\Account;
use Symfony\Contracts\EventDispatcher\Event;

class GetAccountValidPointEvent extends Event
{
    private Account $account;

    private ?float $result = null;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getResult(): ?float
    {
        return $this->result;
    }

    public function setResult(?float $result): void
    {
        $this->result = $result;
    }
}
