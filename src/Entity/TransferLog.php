<?php

namespace CreditBundle\Entity;

use AntdCpBundle\Builder\Action\ModalFormAction;
use AntdCpBundle\Builder\Field\InputNumberField;
use AntdCpBundle\Builder\Field\LongTextField;
use AntdCpBundle\Service\FormFieldBuilder;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransferLogRepository;
use CreditBundle\Service\TransactionService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\HeaderAction;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\JsonRPC\Core\Exception\ApiException;

#[AsPermission(title: '交易流水（旧）', titleOverrideEnv: 'PAGE_TITLE_TRANSFER_LOG')]
#[ORM\Entity(repositoryClass: TransferLogRepository::class)]
#[ORM\Table(name: 'credit_transaction', options: ['comment' => '交易流水（旧）'])]
class TransferLog
{
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[FormField(span: 6)]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '币种'])]
    private ?string $currency = null;

    #[FormField(title: '转出账户', span: 12)]
    #[ListColumn(title: '转出账户')]
    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $outAccount = null;

    #[FormField(span: 6)]
    #[Groups(['restful_read'])]
    #[ListColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转出金额'])]
    private ?string $outAmount = null;

    #[FormField(title: '转入账户', span: 12)]
    #[ListColumn(title: '转入账户')]
    #[ORM\ManyToOne(targetEntity: Account::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Account $inAccount = null;

    #[FormField(span: 6)]
    #[Groups(['restful_read'])]
    #[ListColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 6, options: ['comment' => '转入金额'])]
    private ?string $inAmount = null;

    #[Groups(['restful_read'])]
    #[FormField]
    #[Keyword]
    #[ListColumn]
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
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $expireTime = null;

    #[CreatedByColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[Groups(['restful_read'])]
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
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

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

    #[HeaderAction(title: '创建交易记录')]
    public function renderMakeAction(FormFieldBuilder $fieldHelper): ModalFormAction
    {
        return ModalFormAction::gen()
            ->setFormTitle('创建交易记录')
            ->setLabel('创建交易记录')
            ->setFormWidth(600)
            ->setFormFields([
                $fieldHelper->createSelectFromEntityClass(Account::class)
                    ->setRules([['required' => true, 'message' => '请选择转出账户']])
                    ->setSpan(16)
                    ->setId('out_account')
                    ->setLabel('转出账户'),
                InputNumberField::gen()
                    ->setSpan(8)
                    ->setId('amount')
                    ->setLabel('转出金额')
                    ->setInputProps([
                        'style' => ['width' => '100%'],
                    ]),

                $fieldHelper->createSelectFromEntityClass(Account::class)
                    ->setRules([['required' => true, 'message' => '请选择转入账户']])
                    ->setSpan(16)
                    ->setId('in_account')
                    ->setLabel('转入账户'),

                LongTextField::gen()
                    ->setId('remark')
                    ->setLabel('备注'),
            ])
            ->setCallback(function (
                array $form,
                array $record,
                TransactionService $transactionService,
                AccountRepository $accountRepository,
            ) {
                $outAccount = $accountRepository->find($form['out_account']);
                if (!$outAccount) {
                    throw new ApiException('找不到转出账号');
                }

                $inAccount = $accountRepository->find($form['in_account']);
                if (!$inAccount) {
                    throw new ApiException('找不到转入账号');
                }

                try {
                    $res = $transactionService->transfer(
                        $outAccount,
                        $inAccount,
                        $form['amount'],
                        $form['remark'],
                    );
                } catch (TransactionException $e) {
                    throw new ApiException($e->getMessage(), $e->getCode(), previous: $e);
                }

                return [
                    '__message' => '交易成功',
                    'form' => $form,
                    'record' => $record,
                    'transaction' => [
                        'id' => $res,
                    ],
                ];
            });
    }

    public function getExpireTime(): ?\DateTimeInterface
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): self
    {
        $this->expireTime = $expireTime;

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
}
