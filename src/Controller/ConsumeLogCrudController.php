<?php

declare(strict_types=1);

namespace CreditBundle\Controller;

use CreditBundle\Entity\ConsumeLog;
use CreditBundle\Entity\Transaction;
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

#[AdminCrud(
    routePath: '/credit/consume_log',
    routeName: 'credit_consume_log'
)]
final class ConsumeLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsumeLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('积分消耗记录')
            ->setEntityLabelInPlural('积分消耗记录管理')
            ->setPageTitle(Crud::PAGE_INDEX, '积分消耗记录列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建积分消耗记录')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑积分消耗记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '积分消耗记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['costTransaction.eventNo', 'consumeTransaction.eventNo', 'costTransaction.account.name', 'consumeTransaction.account.name'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('costTransaction', '成本流水')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('指向增加积分的流水，表示积分是从哪一笔增加的记录中扣除的')
            ->formatValue(function ($value) {
                if ($value instanceof Transaction) {
                    return "#{$value->getId()} - {$value->getEventNo()} ({$value->getAmount()})";
                }

                return '';
            })
        ;

        yield AssociationField::new('consumeTransaction', '消费流水')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setHelp('指向扣积分的流水，表示一次扣分行为')
            ->formatValue(function ($value) {
                if ($value instanceof Transaction) {
                    return "#{$value->getId()} - {$value->getEventNo()} ({$value->getAmount()})";
                }

                return '';
            })
        ;

        yield MoneyField::new('amount', '消耗金额')
            ->setColumns('col-md-6')
            ->setRequired(true)
            ->setCurrency('CNY')
            ->setNumDecimals(2)
            ->setHelp('本次消耗的积分金额')
        ;

        yield TextField::new('createdFromIp', '创建IP')
            ->onlyOnDetail()
            ->setHelp('记录创建时的IP地址')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('记录创建的时间')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('costTransaction')
            ->add('consumeTransaction')
            ->add(DateTimeFilter::new('createTime'))
        ;
    }
}
