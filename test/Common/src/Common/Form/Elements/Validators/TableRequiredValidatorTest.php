<?php

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\TableRequiredValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Test TableRequiredValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TableRequiredValidatorTest extends MockeryTestCase
{
    /**
     * Set up the validator
     */
    public function setUp()
    {
        $this->validator = new TableRequiredValidator();
    }

    /**
     * Test isValid
     *
     * @dataProvider providerIsValid
     */
    public function testIsValid($value, $context, $expected)
    {
        $this->assertEquals($expected, $this->validator->isValid($value, $context));
    }

    /**
     * Provider for isValid
     *
     * @return array
     */
    public function providerIsValid()
    {
        return array(
            // With action
            array(null, array('action' => 'foo', 'rows' => 0), true),
            array(null, array('action' => 'foo', 'rows' => 1), true),
            array(null, array('action' => 'foo', 'rows' => 10), true),
            // Without action
            array(null, array('rows' => 0), false),
            array(null, array('rows' => 1), true),
            array(null, array('rows' => 10), true)
        );
    }

    public function testGetSetRowsRequired()
    {
        $validator = new TableRequiredValidator(['rowsRequired' => 2]);

        $this->assertEquals(true, $validator->isValid(null, ['rows' => 2]));
        $this->assertEquals(false, $validator->isValid(null, ['rows' => 1]));
    }
}
