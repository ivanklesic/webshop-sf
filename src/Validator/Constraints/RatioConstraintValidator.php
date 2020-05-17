<?php


namespace App\Validator\Constraints;

use App\Entity\Product as Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RatioConstraintValidator extends ConstraintValidator
{
    public function validate($product, Constraint $constraint)
    {
        if (!$product instanceof Product) {
            throw new UnexpectedTypeException($constraint, RatioConstraint::class);
        }


        if ($product->getCarbohydratePercent() + $product->getProteinPercent() + $product->getLipidPercent() != 100 ) {

            $this->context->buildViolation($constraint->message)
                ->atPath('proteinPercent')
                ->addViolation();
        }
    }

}