<?php

declare(strict_types=1);

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\TransferLog;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

#[AdminCrud(
    routePath: '/credit/transfer_log',
    routeName: 'credit_transfer_log'
)]
final class TransferLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TransferLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('积分转账记录')
            ->setEntityLabelInPlural('积分转账记录管理')
            ->setPageTitle(Crud::PAGE_INDEX, '积分转账记录列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建积分转账记录')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑积分转账记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '积分转账记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['currency', 'remark', 'relationId', 'relationModel'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('currency', '币种')
            ->setColumns('col-md-4')
            ->setRequired(true)
            ->setMaxLength(20)
        ;

        yield AssociationField::new('outAccount', '转出账户')
            ->setColumns('col-md-4')
            ->autocomplete()
        ;

        yield AssociationField::new('inAccount', '转入账户')
            ->setColumns('col-md-4')
            ->autocomplete()
        ;

        yield MoneyField::new('outAmount', '转出金额')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
        ;

        yield MoneyField::new('inAmount', '转入金额')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
        ;

        yield TextField::new('remark', '备注')
            ->setColumns('col-md-12')
            ->setMaxLength(100)
        ;

        yield TextField::new('relationId', '关联ID')
            ->setColumns('col-md-6')
            ->setMaxLength(120)
        ;

        yield TextField::new('relationModel', '关联模型')
            ->setColumns('col-md-6')
            ->setMaxLength(200)
        ;

        yield DateTimeField::new('expireTime', '过期时间')
            ->setColumns('col-md-6')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
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
            ->add(TextFilter::new('currency', '币种'))
            ->add('outAccount')
            ->add('inAccount')
            ->add(TextFilter::new('remark', '备注'))
            ->add(TextFilter::new('relationId', '关联ID'))
            ->add(TextFilter::new('relationModel', '关联模型'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
            ->add(DateTimeFilter::new('expireTime', '过期时间'))
        ;
    }
}
