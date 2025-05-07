<?php

namespace CreditBundle\Entity;

use CreditBundle\Repository\ConsumeLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '积分消耗明细')]
#[ORM\Entity(repositoryClass: ConsumeLogRepository::class)]
#[ORM\Table(name: 'ims_credit_consume_log', options: ['comment' => '积分消耗明细'])]
#[ORM\UniqueConstraint(name: 'credit_consume_log_idx_uniq', columns: ['cost_transaction_id', 'consume_transaction_id'])]
class ConsumeLog
{
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    /**
     * @var Transaction|null 指向增加积分的流水，表示积分是从哪一笔增加的记录中扣除的
     */
    #[ORM\ManyToOne(inversedBy: 'constLogs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transaction $costTransaction = null;

    /**
     * @var Transaction|null 指向扣积分的流水，表示一次扣分行为
     */
    #[ORM\ManyToOne(inversedBy: 'consumeLogs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Transaction $consumeTransaction = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[Groups(['restful_read', 'admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCostTransaction(): ?Transaction
    {
        return $this->costTransaction;
    }

    public function setCostTransaction(?Transaction $costTransaction): static
    {
        $this->costTransaction = $costTransaction;

        return $this;
    }

    public function getConsumeTransaction(): ?Transaction
    {
        return $this->consumeTransaction;
    }

    public function setConsumeTransaction(?Transaction $consumeTransaction): static
    {
        $this->consumeTransaction = $consumeTransaction;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

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

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }
}
