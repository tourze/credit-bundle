<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use CreditBundle\Exception\EntityPropertyNotSetException;
use CreditBundle\Repository\AdjustRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: AdjustRequestRepository::class)]
#[ORM\Table(name: 'credit_adjust_request', options: ['comment' => '积分调整请求'])]
class AdjustRequest implements AdminArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(inversedBy: 'adjustRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[Groups(groups: ['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '变更数值'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $amount;

    #[Groups(groups: ['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Column(type: Types::STRING, length: 30, enumType: AdjustRequestType::class, options: ['comment' => '请求变动类型'])]
    #[Assert\Choice(callback: [AdjustRequestType::class, 'cases'])]
    #[Assert\NotNull]
    private ?AdjustRequestType $type = null;

    #[Groups(groups: ['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Column(type: Types::INTEGER, length: 30, enumType: AdjustRequestStatus::class, options: ['comment' => '状态'])]
    #[Assert\Choice(callback: [AdjustRequestStatus::class, 'cases'])]
    #[Assert\NotNull]
    private ?AdjustRequestStatus $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 255)]
    private ?string $remark = null;

    public function getStatus(): ?AdjustRequestStatus
    {
        return $this->status;
    }

    public function setStatus(AdjustRequestStatus $status): void
    {
        $this->status = $status;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function getType(): ?AdjustRequestType
    {
        return $this->type;
    }

    public function setType(AdjustRequestType $type): void
    {
        $this->type = $type;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function __toString(): string
    {
        return "AdjustRequest #{$this->getId()} - {$this->amount}";
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'amount' => $this->amount,
            'remark' => $this->remark,
            'account' => $this->account,
            'type' => $this->type,
            'status' => $this->status,
            'id' => $this->id,
            'createTime' => $this->createTime,
            'updateTime' => $this->updateTime,
        ];
    }
}
