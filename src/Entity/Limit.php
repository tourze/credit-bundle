<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Enum\LimitType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity]
#[ORM\Table(name: 'credit_limit', options: ['comment' => '配额限制'])]
#[ORM\UniqueConstraint(name: 'credit_limit_idx_uniq', columns: ['account_id', 'type'])]
class Limit implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY', inversedBy: 'limits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Account $account = null;

    #[ORM\Column(type: Types::STRING, length: 30, enumType: LimitType::class, options: ['comment' => '类型'])]
    #[Assert\Choice(callback: [LimitType::class, 'cases'])]
    #[Assert\NotNull]
    private ?LimitType $type = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '限制数量'])]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $value = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 255)]
    private ?string $remark = null;

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        return "{$this->getType()?->getLabel()}: {$this->getValue()}";
    }

    public function getType(): ?LimitType
    {
        return $this->type;
    }

    public function setType(LimitType $type): void
    {
        $this->type = $type;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }
}
