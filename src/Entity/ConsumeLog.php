<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Repository\ConsumeLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;

#[ORM\Entity(repositoryClass: ConsumeLogRepository::class)]
#[ORM\Table(name: 'ims_credit_consume_log', options: ['comment' => '积分消耗明细'])]
#[ORM\UniqueConstraint(name: 'credit_consume_log_idx_uniq', columns: ['cost_transaction_id', 'consume_transaction_id'])]
class ConsumeLog implements \Stringable
{
    use CreateTimeAware;
    use CreatedFromIpAware;
    use SnowflakeKeyAware;

    /**
     * @var Transaction|null 指向增加积分的流水，表示积分是从哪一笔增加的记录中扣除的
     */
    #[ORM\ManyToOne(inversedBy: 'constLogs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transaction $costTransaction = null;

    /**
     * @var Transaction|null 指向扣积分的流水，表示一次扣分行为
     */
    #[ORM\ManyToOne(inversedBy: 'consumeLogs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transaction $consumeTransaction = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '消耗金额'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    private ?string $amount = null;

    public function getCostTransaction(): ?Transaction
    {
        return $this->costTransaction;
    }

    public function setCostTransaction(?Transaction $costTransaction): void
    {
        $this->costTransaction = $costTransaction;
    }

    public function getConsumeTransaction(): ?Transaction
    {
        return $this->consumeTransaction;
    }

    public function setConsumeTransaction(?Transaction $consumeTransaction): void
    {
        $this->consumeTransaction = $consumeTransaction;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function __toString(): string
    {
        return "ConsumeLog #{$this->getId()} - Amount: {$this->getAmount()}";
    }
}
