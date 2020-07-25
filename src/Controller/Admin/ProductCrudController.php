<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id'),
            TextField::new('title'),
            // TextEditorField::new('description'),
            TextField::new('color'),
            NumberField::new('ibu'),
            PercentField::new('alcohol')
                ->setNumDecimals(2)
                ->setStoredAsFractional(false),
            MoneyField::new('price')->setCurrency('EUR')
                ->setNumDecimals(2)
                ->setStoredAsCents(false),
            NumberField::new('quantity'),
            BooleanField::new('availability'),
            AssociationField::new('style'),
            AssociationField::new('country'),
            AssociationField::new('brewery'),
            AssociationField::new('bottle'),
        ];
    }
    
}
