<?php

namespace CreditBundle\Entity;

use CreditBundle\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\ResourceManageBundle\Model\ResourceIdentity;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Table(name: 'credit_point', options: ['comment' => '积分配置'])]
class Currency implements \Stringable, AdminArrayInterface, ResourceIdentity
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 20, unique: true, options: ['comment' => '代码'])]
    private string $currency;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 40, unique: true, options: ['comment' => '名称'])]
    private string $name;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '首选币种', 'default' => 0])]
    private ?bool $main = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'currency', targetEntity: Transaction::class)]
    private Collection $transactions;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

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

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->id) {
            return '';
        }

        return "{$this->getName()}({$this->getCurrency()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): string
    {
        return strval($this->currency);
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
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

    //    /**
    //     * @BeforeCreate()
    //     * @BeforeEdit()
    //     * @return void
    //     */
    //    public function validCode(): void
    //    {
    //        $currencies = require_once __DIR__ . '/../../../vendor/moneyphp/money/resources/currency.php';
    //        if (isset($currencies[$this->getCurrency()])) {
    //            throw new ApiException("编号[{$this->getCurrency()}]是国家货币代码之一，为减少不必要的冲突，请不要使用该代码");
    //        }
    //    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

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
            $transaction->setCurrency($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCurrency() === $this) {
                $transaction->setCurrency(null);
            }
        }

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
    }public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'currency' => $this->getCurrency(),
            'name' => $this->getName(),
        ];
    }

    public function getResourceId(): string
    {
        return (string) $this->getId();
    }

    public function getResourceLabel(): string
    {
        return $this->getName();
    }

    public function getMain(): ?bool
    {
        return $this->main;
    }

    public function setMain(?bool $main): void
    {
        $this->main = $main;
    }
}
