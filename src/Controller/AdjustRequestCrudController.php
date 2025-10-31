<?php

declare(strict_types=1);

namespace CreditBundle\Controller;

use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;

#[AdminCrud(
    routePath: '/credit/adjust_request',
    routeName: 'credit_adjust_request'
)]
final class AdjustRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdjustRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('积分调整请求')
            ->setEntityLabelInPlural('积分调整请求管理')
            ->setPageTitle(Crud::PAGE_INDEX, '积分调整请求列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建积分调整请求')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑积分调整请求')
            ->setPageTitle(Crud::PAGE_DETAIL, '积分调整请求详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['account.username', 'amount', 'remark'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('account', '积分账户')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('选择需要调整积分的账户')
        ;

        yield TextField::new('amount', '调整金额')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('积分调整数量，支持小数点后两位')
        ;

        $typeField = EnumField::new('type', '调整类型');
        $typeField->setEnumCases(AdjustRequestType::cases());
        yield $typeField
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('选择积分调整类型：增加或减少')
        ;

        $statusField = EnumField::new('status', '状态');
        $statusField->setEnumCases(AdjustRequestStatus::cases());
        yield $statusField
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('调整请求的审核状态')
        ;

        yield TextareaField::new('remark', '备注')
            ->setColumns('col-md-12')
            ->setRequired(false)
            ->setHelp('积分调整的原因说明')
            ->hideOnIndex()
        ;

        // 在列表页显示截断的备注
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('remark', '备注')
                ->formatValue(function ($value): string {
                    if (!$value || !is_scalar($value)) {
                        return '-';
                    }
                    $stringValue = (string) $value;
                    if (mb_strlen($stringValue) > 30) {
                        return mb_substr($stringValue, 0, 30) . '...';
                    }

                    return $stringValue;
                })
            ;
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;

        yield AssociationField::new('createdBy', '创建者')
            ->onlyOnDetail()
            ->setHelp('创建该调整请求的管理员')
        ;

        yield AssociationField::new('updatedBy', '更新者')
            ->onlyOnDetail()
            ->setHelp('最后更新该调整请求的管理员')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '积分账户'))
            ->add(
                ChoiceFilter::new('type', '调整类型')
                    ->setChoices([
                        '增加' => AdjustRequestType::INCREASE->value,
                        '减少' => AdjustRequestType::DECREASE->value,
                    ])
            )
            ->add(
                ChoiceFilter::new('status', '状态')
                    ->setChoices([
                        '审核中' => AdjustRequestStatus::EXAMINE->value,
                        '通过' => AdjustRequestStatus::PASS->value,
                        '拒绝' => AdjustRequestStatus::TURN_DOWN->value,
                    ])
            )
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
            ->add(EntityFilter::new('createdBy', '创建者'))
            ->add(EntityFilter::new('updatedBy', '更新者'))
        ;
    }
}
