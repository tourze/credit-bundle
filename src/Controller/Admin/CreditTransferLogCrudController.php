<?php

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\TransferLog;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreditTransferLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TransferLog::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
