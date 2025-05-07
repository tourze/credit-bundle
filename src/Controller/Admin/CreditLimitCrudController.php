<?php

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\Limit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreditLimitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Limit::class;
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
