<?php

declare(strict_types=1);

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\Account;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

#[AdminCrud(
    routePath: '/credit/account',
    routeName: 'credit_account'
)]
final class AccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('积分账户')
            ->setEntityLabelInPlural('积分账户管理')
            ->setPageTitle(Crud::PAGE_INDEX, '积分账户列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建积分账户')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑积分账户')
            ->setPageTitle(Crud::PAGE_DETAIL, '积分账户详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'currency', 'user.username'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield TextField::new('name', '账户名称')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(120)
        ;

        yield TextField::new('currency', '币种代码')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setMaxLength(20)
        ;

        yield AssociationField::new('user', '关联用户')
            ->setColumns('col-md-6')
            ->autocomplete()
        ;

        yield MoneyField::new('endingBalance', '期末余额')
            ->setColumns('col-md-4')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->hideOnForm()
        ;

        yield MoneyField::new('increasedAmount', '增加发生额')
            ->setColumns('col-md-4')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->hideOnForm()
        ;

        yield MoneyField::new('decreasedAmount', '减少发生额')
            ->setColumns('col-md-4')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->hideOnForm()
        ;

        yield MoneyField::new('expiredAmount', '过期发生额')
            ->setColumns('col-md-4')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->onlyOnDetail()
        ;

        yield AssociationField::new('limits', '限制规则')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('transactions', '交易记录')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('adjustRequests', '调整请求')
            ->onlyOnDetail()
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
            ->add(TextFilter::new('name', '账户名称'))
            ->add(TextFilter::new('currency', '币种代码'))
            ->add(EntityFilter::new('user', '用户'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
