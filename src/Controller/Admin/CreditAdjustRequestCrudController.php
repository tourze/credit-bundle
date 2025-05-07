<?php

namespace CreditBundle\Controller\Admin;

use CreditBundle\Entity\AdjustRequest;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CreditAdjustRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdjustRequest::class;
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
