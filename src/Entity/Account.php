<?php

namespace CreditBundle\Entity;

use AntdCpBundle\Builder\Field\DynamicFieldSet;
use CreditBundle\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\BatchDeletable;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\EnumExtra\Itemable;
use Tourze\LockServiceBundle\Model\LockEntity;

/**
 * 在上一版本的设计里，Account这个概念其实是弱化的了，因为基本上都是系统内转账。
 * 但是这样会发生一个问题，就是不好统一不同来源的数据支出情况。例如我们要统计某个抽奖活动总共发放了多少积分。
 * 为此，我们在中间再抽一层账户的概念。
 *
 * 参考银行的设计，我们做成一个币种一个账号，单币种账号统计上会简单一点点。
 *
 * @see https://www.financialnews.com.cn/gc/gz/202107/t20210728_224526.html
 */
#[AsPermission(title: '账户')]
#[Deletable]
#[Editable]
#[Creatable]
#[BatchDeletable]
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'credit_account', options: ['comment' => '账户'])]
#[ORM\UniqueConstraint(name: 'credit_account_idx_uniq', columns: ['name', 'currency_id', 'user_id'])]
class Account implements \Stringable, Itemable, AdminArrayInterface, LockEntity
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[FormField(span: 18)]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => '名称'])]
    private string $name;

    #[FormField(title: '币种', span: 6)]
    #[Filterable(label: '币种', inputWidth: 160)]
    #[ListColumn(title: '币种')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Currency::class, fetch: 'EXTRA_LAZY')]
    private Currency $currency;

    /**
     * 账户不一定跟用户关联的，但为了简化设计，我们还是约束一下
     * 一般来讲，一个用户一个币种应该是唯一的.
     */
    #[FormField(title: '关联用户')]
    #[ListColumn(title: '关联用户')]
    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    /**
     * @DynamicFieldSet()
     *
     * @var Collection<Limit>
     */
    #[FormField(title: '交易限制')]
    #[ListColumn(title: '交易限制')]
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Limit::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $limits;

    #[ListColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '期末余额'])]
    private ?string $endingBalance = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Transaction::class)]
    private Collection $transactions;

    /**
     * @var Collection<int, AdjustRequest>
     */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: AdjustRequest::class)]
    private Collection $adjustRequests;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '增加发生额'])]
    private ?string $increasedAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '减少发生额'])]
    private ?string $decreasedAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '过期发生额'])]
    private ?string $expiredAmount = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function __construct()
    {
        $this->limits = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->adjustRequests = new ArrayCollection();

        $this->setEndingBalance(0);
        $this->setIncreasedAmount(0);
        $this->setDecreasedAmount(0);
        $this->setExpiredAmount(0);
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getCurrency()} - {$this->getName()}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return strval($this->name);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Limit>
     */
    public function getLimits(): Collection
    {
        return $this->limits;
    }

    public function addLimit(Limit $limit): self
    {
        if (!$this->limits->contains($limit)) {
            $this->limits[] = $limit;
            $limit->setAccount($this);
        }

        return $this;
    }

    public function removeLimit(Limit $limit): self
    {
        if ($this->limits->removeElement($limit)) {
            // set the owning side to null (unless already changed)
            if ($limit->getAccount() === $this) {
                $limit->setAccount(null);
            }
        }

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function toSelectItem(): array
    {
        return [
            'label' => "{$this->getCurrency()} - {$this->getName()}",
            'text' => "{$this->getCurrency()} - {$this->getName()}",
            'value' => $this->getId(),
        ];
    }

    public function getEndingBalance(): ?string
    {
        return $this->endingBalance;
    }

    public function setEndingBalance(string|float|null $endingBalance): static
    {
        $this->endingBalance = $endingBalance;

        return $this;
    }

    /**
     * @return Collection<int, AdjustRequest>
     */
    public function getAdjustRequests(): Collection
    {
        return $this->adjustRequests;
    }

    public function addAdjustRequests(AdjustRequest $adjustRequests): static
    {
        if (!$this->adjustRequests->contains($adjustRequests)) {
            $this->adjustRequests->add($adjustRequests);
            $adjustRequests->setAccount($this);
        }

        return $this;
    }

    public function removeAdjustRequests(AdjustRequest $adjustRequests): static
    {
        if ($this->adjustRequests->removeElement($adjustRequests)) {
            // set the owning side to null (unless already changed)
            if ($adjustRequests->getAccount() === $this) {
                $adjustRequests->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

        return $this;
    }

    public function getIncreasedAmount(): ?string
    {
        return $this->increasedAmount;
    }

    public function setIncreasedAmount(string|float|null $increasedAmount): static
    {
        $this->increasedAmount = $increasedAmount;

        return $this;
    }

    public function getDecreasedAmount(): ?string
    {
        return $this->decreasedAmount;
    }

    public function setDecreasedAmount(string|float|null $decreasedAmount): static
    {
        $this->decreasedAmount = $decreasedAmount;

        return $this;
    }

    public function getExpiredAmount(): ?string
    {
        return $this->expiredAmount;
    }

    public function setExpiredAmount(?string $expiredAmount): static
    {
        $this->expiredAmount = $expiredAmount;

        return $this;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function retrieveLockResource(): string
    {
        return "credit_account_{$this->getId()}";
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'currency' => $this->getCurrency()->retrieveAdminArray(),
            'endingBalance' => $this->getEndingBalance(),
            'increasedAmount' => $this->getIncreasedAmount(),
            'decreasedAmount' => $this->getDecreasedAmount(),
        ];
    }
}
