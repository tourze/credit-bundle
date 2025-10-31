<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use BenefitBundle\Model\BenefitResource;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'ims_credit_account_transaction', options: ['comment' => '交易流水'])]
#[ORM\UniqueConstraint(name: 'ims_credit_account_transaction_idx_uniq', columns: ['event_no', 'account_id'])]
#[ORM\Index(name: 'ims_credit_account_transaction_fifo_idx', columns: ['account_id', 'amount', 'balance', 'expire_time'])]
#[ORM\Index(name: 'ims_credit_account_transaction_balance_idx', columns: ['account_id', 'balance'])]
class Transaction implements AdminArrayInterface, BenefitResource, \Stringable
{
    use CreatedFromIpAware;
    use TimestampableAware;
    use SnowflakeKeyAware;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    #[Assert\Type(type: 'array')]
    private ?array $context = [];

    #[IndexColumn]
    #[ORM\Column(length: 50, options: ['comment' => '事件编号'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $eventNo;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[IndexColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '变动流水'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 13)]
    private string $amount;

    #[IndexColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '余额'])]
    #[Assert\Length(max: 13)]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 100)]
    private ?string $remark = null;

    #[IndexColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '关联第三方id'])]
    #[Assert\Length(max: 120)]
    private ?string $relationId = null;

    #[IndexColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '关联模型类'])]
    #[Assert\Length(max: 200)]
    private ?string $relationModel = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '币种代码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $currency;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $expireTime = null;

    /**
     * @var Collection<int, ConsumeLog> 当是支出流水时，我们需要记录具体是那一笔收入进行了支出扣减
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'costTransaction', targetEntity: ConsumeLog::class)]
    private Collection $constLogs;

    /**
     * @var Collection<int, ConsumeLog> 记录是消耗了哪一笔收入
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'consumeTransaction', targetEntity: ConsumeLog::class)]
    private Collection $consumeLogs;

    public function __construct()
    {
        $this->constLogs = new ArrayCollection();
        $this->consumeLogs = new ArrayCollection();
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

    public function getEventNo(): string
    {
        return $this->eventNo;
    }

    public function setEventNo(string $eventNo): void
    {
        $this->eventNo = $eventNo;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): void
    {
        $this->balance = $balance;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getRelationId(): ?string
    {
        return $this->relationId;
    }

    public function setRelationId(?string $relationId): void
    {
        $this->relationId = $relationId;
    }

    public function getRelationModel(): ?string
    {
        return $this->relationModel;
    }

    public function setRelationModel(?string $relationModel): void
    {
        $this->relationModel = $relationModel;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'eventNo' => $this->getEventNo(),
            'account' => $this->getAccount()->retrieveAdminArray(),
            'amount' => $this->getAmount(),
            'remark' => $this->getRemark(),
            'currency' => $this->getCurrency(),
            'bizUser' => [],
        ];
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): void
    {
        if ($expireTime instanceof \DateTime) {
            $this->expireTime = \DateTimeImmutable::createFromMutable($expireTime);
        } elseif ($expireTime instanceof \DateTimeImmutable) {
            $this->expireTime = $expireTime;
        } else {
            $this->expireTime = null;
        }
    }

    /**
     * @return Collection<int, ConsumeLog>
     */
    public function getConstLogs(): Collection
    {
        return $this->constLogs;
    }

    public function addCostLog(ConsumeLog $consumeLog): void
    {
        if (!$this->constLogs->contains($consumeLog)) {
            $this->constLogs->add($consumeLog);
            $consumeLog->setCostTransaction($this);
        }
    }

    public function removeCostLog(ConsumeLog $consumeLog): void
    {
        if ($this->constLogs->removeElement($consumeLog)) {
            // set the owning side to null (unless already changed)
            if ($consumeLog->getCostTransaction() === $this) {
                $consumeLog->setCostTransaction(null);
            }
        }
    }

    /**
     * @return Collection<int, ConsumeLog>
     */
    public function getConsumeLogs(): Collection
    {
        return $this->consumeLogs;
    }

    public function addConsumeLog(ConsumeLog $consumeLog): void
    {
        if (!$this->consumeLogs->contains($consumeLog)) {
            $this->consumeLogs->add($consumeLog);
            $consumeLog->setConsumeTransaction($this);
        }
    }

    public function removeConsumeLog(ConsumeLog $consumeLog): void
    {
        if ($this->consumeLogs->removeElement($consumeLog)) {
            // set the owning side to null (unless already changed)
            if ($consumeLog->getConsumeTransaction() === $this) {
                $consumeLog->setConsumeTransaction(null);
            }
        }
    }

    public function __toString(): string
    {
        return "Transaction #{$this->getId()} - {$this->getAmount()} {$this->getCurrency()}";
    }
}
