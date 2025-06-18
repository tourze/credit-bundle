<?php

namespace CreditBundle\Entity;

use CreditBundle\Repository\TransferErrorLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;

#[ORM\Entity(repositoryClass: TransferErrorLogRepository::class, readOnly: true)]
#[ORM\Table(name: 'ims_credit_transfer_error_log', options: ['comment' => '转账出错日志'])]
class TransferErrorLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文'])]
    private ?array $context = [];

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '转出账号ID'])]
    private ?string $fromAccountId = null;

    #[ORM\Column(length: 120, options: ['comment' => '转出账号名'])]
    private ?string $fromAccountName = null;

    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '转入账号ID'])]
    private ?string $toAccountId = null;

    #[ORM\Column(length: 120, options: ['comment' => '转入账号名'])]
    private ?string $toAccountName = null;

    #[ORM\Column(length: 10, options: ['comment' => '货币'])]
    private ?string $currency = null;

    #[ORM\Column(nullable: true, options: ['comment' => '数值'])]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '异常'])]
    private ?string $exception = null;

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromAccountId(): ?string
    {
        return $this->fromAccountId;
    }

    public function setFromAccountId(string $fromAccountId): static
    {
        $this->fromAccountId = $fromAccountId;

        return $this;
    }

    public function getFromAccountName(): ?string
    {
        return $this->fromAccountName;
    }

    public function setFromAccountName(string $fromAccountName): static
    {
        $this->fromAccountName = $fromAccountName;

        return $this;
    }

    public function getToAccountId(): ?string
    {
        return $this->toAccountId;
    }

    public function setToAccountId(string $toAccountId): static
    {
        $this->toAccountId = $toAccountId;

        return $this;
    }

    public function getToAccountName(): ?string
    {
        return $this->toAccountName;
    }

    public function setToAccountName(string $toAccountName): static
    {
        $this->toAccountName = $toAccountName;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getException(): ?string
    {
        return $this->exception;
    }

    public function setException(?string $exception): static
    {
        $this->exception = $exception;

        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }
}
