<?php

namespace CreditBundle\Entity;

use BenefitBundle\Model\BenefitResource;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'ims_credit_account_transaction', options: ['comment' => '交易流水'])]
#[ORM\UniqueConstraint(name: 'ims_credit_account_transaction_idx_uniq', columns: ['event_no', 'account_id'])]
#[ORM\Index(columns: ['account_id', 'amount', 'balance', 'expire_time'], name: 'ims_credit_transaction_fifo_idx')]
#[ORM\Index(columns: ['account_id', 'balance'], name: 'ims_credit_transaction_balance_idx')]
class Transaction implements AdminArrayInterface, BenefitResource, \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    private ?array $context = [];

    #[IndexColumn]
    #[ORM\Column(length: 50, options: ['comment' => '事件编号'])]
    private string $eventNo;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[IndexColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '变动流水'])]
    private string $amount;

    #[IndexColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '余额'])]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[IndexColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '关联第三方id'])]
    private ?string $relationId = null;

    #[IndexColumn]
    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '关联模型类'])]
    private ?string $relationModel = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
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

    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    public function __construct()
    {
        $this->constLogs = new ArrayCollection();
        $this->consumeLogs = new ArrayCollection();
    }


    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getEventNo(): string
    {
        return $this->eventNo;
    }

    public function setEventNo(string $eventNo): static
    {
        $this->eventNo = $eventNo;

        return $this;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): static
    {
        $this->remark = $remark;

        return $this;
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
            'currency' => $this->getCurrency()->retrieveAdminArray(),
            'bizUser' => [],
        ];
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): static
    {
        if ($expireTime instanceof \DateTime) {
            $this->expireTime = \DateTimeImmutable::createFromMutable($expireTime);
        } elseif ($expireTime instanceof \DateTimeImmutable) {
            $this->expireTime = $expireTime;
        } else {
            $this->expireTime = null;
        }

        return $this;
    }

    /**
     * @return Collection<int, ConsumeLog>
     */
    public function getConstLogs(): Collection
    {
        return $this->constLogs;
    }

    public function addCostLog(ConsumeLog $consumeLog): static
    {
        if (!$this->constLogs->contains($consumeLog)) {
            $this->constLogs->add($consumeLog);
            $consumeLog->setCostTransaction($this);
        }

        return $this;
    }

    public function removeCostLog(ConsumeLog $consumeLog): static
    {
        if ($this->constLogs->removeElement($consumeLog)) {
            // set the owning side to null (unless already changed)
            if ($consumeLog->getCostTransaction() === $this) {
                $consumeLog->setCostTransaction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConsumeLog>
     */
    public function getConsumeLogs(): Collection
    {
        return $this->consumeLogs;
    }

    public function addConsumeLog(ConsumeLog $consumeLog): static
    {
        if (!$this->consumeLogs->contains($consumeLog)) {
            $this->consumeLogs->add($consumeLog);
            $consumeLog->setConsumeTransaction($this);
        }

        return $this;
    }

    public function removeConsumeLog(ConsumeLog $consumeLog): static
    {
        if ($this->consumeLogs->removeElement($consumeLog)) {
            // set the owning side to null (unless already changed)
            if ($consumeLog->getConsumeTransaction() === $this) {
                $consumeLog->setConsumeTransaction(null);
            }
        }

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }

    public function __toString(): string
    {
        return "Transaction #{$this->getId()} - {$this->getAmount()} {$this->getCurrency()}";
    }
}
