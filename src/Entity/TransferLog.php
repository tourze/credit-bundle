<?php

namespace CreditBundle\Entity;

use CreditBundle\Repository\TransferLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: TransferLogRepository::class)]
#[ORM\Table(name: 'credit_transaction', options: ['comment' => '交易流水（旧）'])]
class TransferLog implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '币种'])]
    private ?string $currency = null;

    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $outAccount = null;

    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转出金额'])]
    private ?string $outAmount = null;

    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $inAccount = null;

    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转入金额'])]
    private ?string $inAmount = null;

    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[IndexColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '关联第三方id'])]
    private ?string $relationId = null;

    #[IndexColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '关联模型类'])]
    private ?string $relationModel = null;

    #[Groups(['restful_read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeImmutable $expireTime = null;


    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getOutAccount(): ?Account
    {
        return $this->outAccount;
    }

    public function setOutAccount(?Account $outAccount): self
    {
        $this->outAccount = $outAccount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getOutAmount(): ?string
    {
        return $this->outAmount;
    }

    public function setOutAmount(string $outAmount): self
    {
        $this->outAmount = $outAmount;

        return $this;
    }

    public function getInAccount(): ?Account
    {
        return $this->inAccount;
    }

    public function setInAccount(?Account $inAccount): self
    {
        $this->inAccount = $inAccount;

        return $this;
    }

    public function getInAmount(): ?string
    {
        return $this->inAmount;
    }

    public function setInAmount(string $inAmount): self
    {
        $this->inAmount = $inAmount;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): self
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

    public function __toString(): string
    {
        return "TransferLog #{$this->getId()} - {$this->getCurrency()} Out: {$this->getOutAmount()} In: {$this->getInAmount()}";
    }
}
