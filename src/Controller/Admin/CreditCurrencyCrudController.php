<?php

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\Currency;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreditCurrencyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Currency::class;
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
