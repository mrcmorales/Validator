<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraints\AbstractCompositeValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;


/**
 * @author Marc Morera Merino <hyuhu@mmoreram.com>
 * @author Marc Morales Valldepérez <marcmorales83@gmail.com>
 *
 * @api
 */
class SomeValidator extends AbstractCompositeValidator
{

    /**
     * {@inheritDoc}
     */
    public function doValidate($value, Constraint $constraint)
    {
        $group = $this->context->getGroup();

        $totalIterations = count($value) * count($constraint->constraints);

        foreach ($value as $key => $element) {
            foreach ($constraint->constraints as $constr) {
                $this->context->validateValue($element, $constr, '[' . $key . ']', $group);
            }
        }

        $constraintsSuccess = $totalIterations - (int) $this->context->getViolations()->count();

        /**
         * We clear all violations as just current Validator should add real Violations
         */
        $this->context->clearViolations();

        if (isset($constraint->exactly) && $constraintsSuccess != $constraint->exactly){

            $this->context->addViolation($constraint->exactlyMessage, array(
                '{{ limit }}' => $constraint->exactly,
            ), null, true);

            return;
        }

        if (isset($constraint->min) && $constraintsSuccess < $constraint->min){
            $this->context->addViolation($constraint->minMessage, array(
                    '{{ limit }}' => $constraint->min,
            ));

            return;
        }

        if (isset($constraint->max) && $constraintsSuccess > $constraint->max){
            $this->context->addViolation($constraint->maxMessage, array(
                '{{ limit }}' => $constraint->max,
            ), null, true);
        }
    }
}
