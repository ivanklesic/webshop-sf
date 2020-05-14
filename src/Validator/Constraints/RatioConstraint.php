<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class RatioConstraint extends Constraint
{
    public $message = 'The macronutrient ratio elements must add up to 100% and contain all positive values.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}