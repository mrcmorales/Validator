<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Each;
use Symfony\Component\Validator\Constraints\EachValidator;

/**
 * @author Marc Morera Merino <hyuhu@mmoreram.com>
 * @author Marc Morales Valldepérez <marcmorales83@gmail.com>
 *
 * @api
 */
class EachValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new EachValidator();
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue('MyGroup'));
    }

    protected function tearDown()
    {
        $this->validator = null;
        $this->context = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Each(new Range(array('min' => 4))));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testThrowsExceptionIfNotTraversable()
    {
        $this->validator->validate('foo.barbar', new Each(new Range(array('min' => 4))));
    }

    /**
     * @dataProvider getValidArguments
     */
    public function testWalkSingleConstraint($array)
    {
        $constraint = new Range(array('min' => 4));

        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint, '['.$key.']', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($array, new Each($constraint));
    }

    /**
     * @dataProvider getValidArguments
     */
    public function testWalkMultipleConstraints($array)
    {
        $constraint1 = new Range(array('min' => 4));
        $constraint2 = new NotNull();

        $constraints = array($constraint1, $constraint2);
        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint1, '['.$key.']', 'MyGroup');
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint2, '['.$key.']', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($array, new Each($constraints));
    }

    public function getValidArguments()
    {
        return array(
            array(array(5, 6, 7)),
            array(new \ArrayObject(array(5, 6, 7))),
        );
    }
}
