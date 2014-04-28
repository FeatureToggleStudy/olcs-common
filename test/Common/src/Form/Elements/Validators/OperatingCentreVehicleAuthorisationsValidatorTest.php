<?php

/**
 * Test OperatingCentreVehicleAuthorisationsValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\OperatingCentreVehicleAuthorisationsValidator;

/**
 * Test OperatingCentreVehicleAuthorisationsValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentreVehicleAuthorisationsValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up the validator
     */
    public function setUp()
    {
        $this->validator = new OperatingCentreVehicleAuthorisationsValidator();
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
            // No OCs
            array(0, array('noOfOperatingCentres' => 0, 'minVehicleAuth' => 0, 'maxVehicleAuth' => 0), true),
            array(1, array('noOfOperatingCentres' => 0, 'minVehicleAuth' => 0, 'maxVehicleAuth' => 0), false),
            // 1 OC
            array(9, array('noOfOperatingCentres' => 1, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 10), false),
            array(11, array('noOfOperatingCentres' => 1, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 10), false),
            array(10, array('noOfOperatingCentres' => 1, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 10), true),
            // Multiple OC's
            array(9, array('noOfOperatingCentres' => 5, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 50), false),
            array(10, array('noOfOperatingCentres' => 5, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 50), true),
            array(30, array('noOfOperatingCentres' => 5, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 50), true),
            array(50, array('noOfOperatingCentres' => 5, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 50), true),
            array(51, array('noOfOperatingCentres' => 5, 'minVehicleAuth' => 10, 'maxVehicleAuth' => 50), false)
        );
    }
}
