<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
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
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'credit_account', options: ['comment' => '账户'])]
#[ORM\UniqueConstraint(name: 'credit_account_idx_uniq', columns: ['name', 'currency', 'user_id'])]
class Account implements \Stringable, Itemable, AdminArrayInterface, LockEntity
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => '名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: false, options: ['comment' => '币种代码'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $currency;

    /**
     * 账户不一定跟用户关联的，但为了简化设计，我们还是约束一下
     * 一般来讲，一个用户一个币种应该是唯一的.
     */
    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    /**
     * @var Collection<int, Limit>
     */
    #[ORM\OneToMany(targetEntity: Limit::class, mappedBy: 'account', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $limits;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '期末余额'])]
    #[Assert\Length(max: 255)]
    private ?string $endingBalance = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'account')]
    private Collection $transactions;

    /**
     * @var Collection<int, AdjustRequest>
     */
    #[ORM\OneToMany(targetEntity: AdjustRequest::class, mappedBy: 'account')]
    private Collection $adjustRequests;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '增加发生额'])]
    #[Assert\Length(max: 255)]
    private ?string $increasedAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '减少发生额'])]
    #[Assert\Length(max: 255)]
    private ?string $decreasedAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '过期发生额'])]
    #[Assert\Length(max: 255)]
    private ?string $expiredAmount = null;

    public function __construct()
    {
        $this->limits = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->adjustRequests = new ArrayCollection();

        $this->setEndingBalance(0);
        $this->setIncreasedAmount(0);
        $this->setDecreasedAmount(0);
        $this->setExpiredAmount('0');
    }

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return "{$this->getCurrency()} - {$this->getName()}";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return strval($this->name);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection<int, Limit>
     */
    public function getLimits(): Collection
    {
        return $this->limits;
    }

    public function addLimit(Limit $limit): void
    {
        if (!$this->limits->contains($limit)) {
            $this->limits->add($limit);
            $limit->setAccount($this);
        }
    }

    public function removeLimit(Limit $limit): void
    {
        if ($this->limits->removeElement($limit)) {
            // set the owning side to null (unless already changed)
            if ($limit->getAccount() === $this) {
                $limit->setAccount(null);
            }
        }
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return array<string, mixed>
     */
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

    public function setEndingBalance(string|float|null $endingBalance): void
    {
        $this->endingBalance = is_numeric($endingBalance) ? (string) $endingBalance : $endingBalance;
    }

    /**
     * @return Collection<int, AdjustRequest>
     */
    public function getAdjustRequests(): Collection
    {
        return $this->adjustRequests;
    }

    public function addAdjustRequests(AdjustRequest $adjustRequests): void
    {
        if (!$this->adjustRequests->contains($adjustRequests)) {
            $this->adjustRequests->add($adjustRequests);
            $adjustRequests->setAccount($this);
        }
    }

    public function removeAdjustRequests(AdjustRequest $adjustRequests): void
    {
        if ($this->adjustRequests->removeElement($adjustRequests)) {
            // set the owning side to null (unless already changed)
            // set the owning side to null (unless already changed)
            // Note: We cannot set null on a required relationship
        }
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAccount($this);
        }
    }

    public function removeTransaction(Transaction $transaction): void
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            // set the owning side to null (unless already changed)
            // Note: We cannot set null on a required relationship
        }
    }

    public function getIncreasedAmount(): ?string
    {
        return $this->increasedAmount;
    }

    public function setIncreasedAmount(string|float|null $increasedAmount): void
    {
        $this->increasedAmount = is_numeric($increasedAmount) ? (string) $increasedAmount : $increasedAmount;
    }

    public function getDecreasedAmount(): ?string
    {
        return $this->decreasedAmount;
    }

    public function setDecreasedAmount(string|float|null $decreasedAmount): void
    {
        $this->decreasedAmount = is_numeric($decreasedAmount) ? (string) $decreasedAmount : $decreasedAmount;
    }

    public function getExpiredAmount(): ?string
    {
        return $this->expiredAmount;
    }

    public function setExpiredAmount(string|float|null $expiredAmount): void
    {
        $this->expiredAmount = is_numeric($expiredAmount) ? (string) $expiredAmount : $expiredAmount;
    }

    public function retrieveLockResource(): string
    {
        return "credit_account_{$this->getId()}";
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
            'currency' => $this->getCurrency(),
            'endingBalance' => $this->getEndingBalance(),
            'increasedAmount' => $this->getIncreasedAmount(),
            'decreasedAmount' => $this->getDecreasedAmount(),
        ];
    }
}
