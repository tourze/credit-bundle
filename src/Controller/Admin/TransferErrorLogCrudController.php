<?php

declare(strict_types=1);

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\TransferErrorLog;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * 转账错误日志管理控制器
 */
#[AdminCrud(
    routePath: '/credit/transfer_error_log',
    routeName: 'credit_transfer_error_log'
)]
final class TransferErrorLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TransferErrorLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('转账错误日志')
            ->setEntityLabelInPlural('转账错误日志')
            ->setPageTitle('index', '转账错误日志管理')
            ->setPageTitle('detail', '转账错误详情')
            ->setHelp('index', '查看转账过程中发生的错误记录，用于排查转账失败问题')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['fromAccountName', 'toAccountName', 'currency', 'exception'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // ID 字段
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->onlyOnIndex()
        ;

        // 转出账号信息
        yield TextField::new('fromAccountId', '转出账号ID')
            ->setColumns('col-md-6')
            ->setHelp('转出账户的唯一标识')
            ->hideOnIndex()
        ;

        yield TextField::new('fromAccountName', '转出账号名')
            ->setColumns('col-md-6')
            ->setHelp('转出账户的显示名称')
        ;

        // 转入账号信息
        yield TextField::new('toAccountId', '转入账号ID')
            ->setColumns('col-md-6')
            ->setHelp('转入账户的唯一标识')
            ->hideOnIndex()
        ;

        yield TextField::new('toAccountName', '转入账号名')
            ->setColumns('col-md-6')
            ->setHelp('转入账户的显示名称')
        ;

        // 转账信息
        yield TextField::new('currency', '货币')
            ->setColumns('col-md-4')
            ->setHelp('转账使用的货币类型')
        ;

        yield NumberField::new('amount', '转账金额')
            ->setColumns('col-md-4')
            ->setHelp('转账的数值金额')
            ->setNumDecimals(2)
            ->formatValue(function ($value) {
                return null !== $value && is_numeric($value) ? number_format((float) $value, 2) : '';
            })
        ;

        // 异常信息 - 在列表页显示摘要，详情页显示完整信息
        yield TextField::new('exception', '错误摘要')
            ->onlyOnIndex()
            ->formatValue(function ($value) {
                if (!$value || !is_string($value)) {
                    return '';
                }

                // 只显示异常信息的前120个字符
                return mb_strlen($value) > 120 ? mb_substr($value, 0, 120) . '...' : $value;
            })
        ;

        yield TextareaField::new('exception', '异常信息')
            ->setColumns('col-md-12')
            ->hideOnIndex()
            ->setNumOfRows(6)
            ->setHelp('详细的异常信息和堆栈跟踪')
        ;

        // 上下文信息 - JSON 数据
        yield ArrayField::new('context', '上下文')
            ->onlyOnDetail()
            ->setHelp('转账时的上下文数据，包含相关的业务参数')
        ;

        // 创建时间
        yield DateTimeField::new('createTime', '发生时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->setHelp('错误发生的时间')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL])
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('fromAccountName', '转出账号名'))
            ->add(TextFilter::new('toAccountName', '转入账号名'))
            ->add(TextFilter::new('currency', '货币'))
            ->add(NumericFilter::new('amount', '转账金额'))
            ->add(TextFilter::new('exception', '异常信息'))
            ->add(DateTimeFilter::new('createTime', '发生时间'))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->orderBy('entity.id', 'DESC')
        ;
    }
}
