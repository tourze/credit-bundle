<?php

declare(strict_types=1);

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;

#[AdminCrud(
    routePath: '/credit/limit',
    routeName: 'credit_limit'
)]
final class LimitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Limit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('配额限制')
            ->setEntityLabelInPlural('配额限制管理')
            ->setPageTitle(Crud::PAGE_INDEX, '配额限制列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建配额限制')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑配额限制')
            ->setPageTitle(Crud::PAGE_DETAIL, '配额限制详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['account.username', 'remark'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('account', '账户')
            ->setColumns('col-md-6')
            ->setRequired(true)
        ;

        $typeField = EnumField::new('type', '类型');
        $typeField->setEnumCases(LimitType::cases());
        yield $typeField
            ->setColumns('col-md-6')
            ->setRequired(true)
        ;

        yield IntegerField::new('value', '限制数量')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('设置配额限制的数量值')
        ;

        yield TextField::new('remark', '备注')
            ->setColumns('col-md-12')
            ->setMaxLength(255)
            ->setRequired(false)
            ->setHelp('可选的备注信息，最多255个字符')
        ;

        yield TextField::new('createdFromIp', '创建IP')
            ->onlyOnDetail()
        ;

        yield TextField::new('updatedFromIp', '更新IP')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield AssociationField::new('createdBy', '创建者')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('updatedBy', '更新者')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('account')
            ->add(
                ChoiceFilter::new('type')
                    ->setChoices([
                        '总限制转出' => LimitType::TOTAL_OUT_LIMIT->value,
                        '每日限制转出' => LimitType::DAILY_OUT_LIMIT->value,
                        '每日限制转入' => LimitType::DAILY_IN_LIMIT->value,
                        '信用额度' => LimitType::CREDIT_LIMIT->value,
                    ])
            )
            ->add('value')
            ->add(DateTimeFilter::new('createTime'))
            ->add(DateTimeFilter::new('updateTime'))
        ;
    }
}
