<?php

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\ConsumeLog;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreditConsumeLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConsumeLog::class;
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
