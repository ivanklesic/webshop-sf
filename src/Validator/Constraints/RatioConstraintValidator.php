<?php


namespace App\Validator\Constraints;

use App\Entity\Diet;
use App\Entity\Product as Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RatioConstraintValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (!$object instanceof Product && !$object instanceof Diet) {
            throw new UnexpectedTypeException($constraint, RatioConstraint::class);
        }


        if ($object->getCarbohydratePercent() + $object->getProteinPercent() + $object->getLipidPercent() != 100 ) {

            $this->context->buildViolation($constraint->message)
                ->atPath('proteinPercent')
                ->addViolation();
        }
    }

}