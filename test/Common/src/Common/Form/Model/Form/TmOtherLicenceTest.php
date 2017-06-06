<?php

namespace CommonTest\Form\Model\Form;

use Olcs\TestHelpers\FormTester\AbstractFormValidationTestCase;

/**
 * Class TmOtherLicenceTest
 *
 * @group FormTests
 */
class TmOtherLicenceTest extends AbstractFormValidationTestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName = \Common\Form\Model\Form\TmOtherLicence::class;

    public function testId()
    {
        $element = ['data', 'id'];
        $this->assertFormElementHidden($element);
    }

    public function testVersion()
    {
        $element = ['data', 'version'];
        $this->assertFormElementHidden($element);
    }

    public function testRedirectAction()
    {
        $element = ['data', 'redirectAction'];
        $this->assertFormElementHidden($element);
    }

    public function testRedirectId()
    {
        $element = ['data', 'redirectId'];
        $this->assertFormElementHidden($element);
    }

    public function testLicNo()
    {
        $element = ['data', 'licNo'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 18);
    }

    public function testRole()
    {
        $element = ['data', 'role'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementDynamicSelect($element, false);
    }

    public function testOperatingCentres()
    {
        $element = ['data', 'operatingCentres'];
        $this->assertFormElementRequired($element, true);
        $this->assertFormElementAllowEmpty($element, false);
        $this->assertFormElementText($element, 0, 255);
    }

    public function testTotalAuthVehicles()
    {
        $element = ['data', 'totalAuthVehicles'];
        $this->assertFormElementRequired($element, false);
        $this->assertFormElementAllowEmpty($element, true);
        $this->assertFormElementNumber($element);
    }

    public function testHoursPerWeek()
    {
        $element = ['data', 'hoursPerWeek'];
        $this->assertFormElementRequired(
            $element,
            true,
            [
                \Zend\Validator\NotEmpty::IS_EMPTY,
                \Zend\I18n\Validator\IsFloat::INVALID,
            ]
        );
        $this->assertFormElementAllowEmpty(
            $element,
            false,
            [],
            [
                \Zend\Validator\NotEmpty::IS_EMPTY,
                \Zend\I18n\Validator\IsFloat::NOT_FLOAT,
            ]
        );
        $this->assertFormElementFloat($element, 0, 99.9);
    }

    public function testSubmit()
    {
        $element = ['form-actions', 'submit'];
        $this->assertFormElementActionButton($element);
    }

    public function testCancel()
    {
        $element = ['form-actions', 'cancel'];
        $this->assertFormElementActionButton($element);
    }
}
