<?php

declare(strict_types=1);

namespace CreditBundle\Entity;

use CreditBundle\Repository\TransferErrorLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;

#[ORM\Entity(repositoryClass: TransferErrorLogRepository::class, readOnly: true)]
#[ORM\Table(name: 'ims_credit_transfer_error_log', options: ['comment' => '转账出错日志'])]
class TransferErrorLog implements \Stringable
{
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    #[Assert\Type(type: 'array')]
    private ?array $context = [];

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '转出账号ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $fromAccountId = null;

    #[ORM\Column(length: 120, options: ['comment' => '转出账号名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $fromAccountName = null;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '转入账号ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $toAccountId = null;

    #[ORM\Column(length: 120, options: ['comment' => '转入账号名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $toAccountName = null;

    #[ORM\Column(length: 10, options: ['comment' => '货币'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    private ?string $currency = null;

    #[ORM\Column(nullable: true, options: ['comment' => '数值'])]
    #[Assert\PositiveOrZero]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '异常'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $exception = null;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getFromAccountId(): ?string
    {
        return $this->fromAccountId;
    }

    public function setFromAccountId(string $fromAccountId): void
    {
        $this->fromAccountId = $fromAccountId;
    }

    public function getFromAccountName(): ?string
    {
        return $this->fromAccountName;
    }

    public function setFromAccountName(string $fromAccountName): void
    {
        $this->fromAccountName = $fromAccountName;
    }

    public function getToAccountId(): ?string
    {
        return $this->toAccountId;
    }

    public function setToAccountId(string $toAccountId): void
    {
        $this->toAccountId = $toAccountId;
    }

    public function getToAccountName(): ?string
    {
        return $this->toAccountName;
    }

    public function setToAccountName(string $toAccountName): void
    {
        $this->toAccountName = $toAccountName;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getException(): ?string
    {
        return $this->exception;
    }

    public function setException(?string $exception): void
    {
        $this->exception = $exception;
    }

    public function __toString(): string
    {
        return "TransferErrorLog #{$this->getId()} - From: {$this->getFromAccountName()} To: {$this->getToAccountName()}";
    }
}
