<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Repository\TransferLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: TransferLogRepository::class)]
#[ORM\Table(name: 'credit_transaction', options: ['comment' => '交易流水（旧）'])]
class TransferLog implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '币种'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $currency = null;

    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $outAccount = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转出金额'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 27)]
    private ?string $outAmount = null;

    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $inAccount = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转入金额'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 27)]
    private ?string $inAmount = null;

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '备注'])]
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

    #[Groups(groups: ['restful_read'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $expireTime = null;

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getOutAccount(): ?Account
    {
        return $this->outAccount;
    }

    public function setOutAccount(?Account $outAccount): void
    {
        $this->outAccount = $outAccount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getOutAmount(): ?string
    {
        return $this->outAmount;
    }

    public function setOutAmount(string $outAmount): void
    {
        $this->outAmount = $outAmount;
    }

    public function getInAccount(): ?Account
    {
        return $this->inAccount;
    }

    public function setInAccount(?Account $inAccount): void
    {
        $this->inAccount = $inAccount;
    }

    public function getInAmount(): ?string
    {
        return $this->inAmount;
    }

    public function setInAmount(string $inAmount): void
    {
        $this->inAmount = $inAmount;
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

    public function __toString(): string
    {
        return "TransferLog #{$this->getId()} - {$this->getCurrency()} Out: {$this->getOutAmount()} In: {$this->getInAmount()}";
    }
}
