<?php

/**
 * Form Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace CommonTest\Service\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Zend\Form\Element\Select;
use Zend\Form\FormInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Form Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FormHelperServiceTest extends MockeryTestCase
{
    public function testAlterElementLabelWithAppend()
    {
        $helper = new FormHelperService();

        $element = m::mock('\stdClass');
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('My labelAppended label');

        $helper->alterElementLabel($element, 'Appended label', 1);
    }

    public function testAlterElementLabelWithNoType()
    {
        $helper = new FormHelperService();

        $element = m::mock('\stdClass');
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('Replaced label');

        $helper->alterElementLabel($element, 'Replaced label');
    }

    public function testAlterElementLabelWithPrepend()
    {
        $helper = new FormHelperService();

        $element = m::mock('\stdClass');
        $element->shouldReceive('getLabel')->andReturn('My label');
        $element->shouldReceive('setLabel')->with('Prepended labelMy label');

        $helper->alterElementLabel($element, 'Prepended label', 2);
    }

    public function testCreateFormWithInvalidForm()
    {
        $helper = new FormHelperService();

        try {
            $helper->createForm('NotFound');
        } catch (\RuntimeException $ex) {
            $this->assertEquals('Form does not exist: NotFound', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testCreateFormWithValidForm()
    {
        $helper = new FormHelperService();

        $form = m::mock('Common\Form\Model\Form\MyFakeFormTest');

        $form->shouldReceive('add')
            ->with(
                array(
                    'type' => 'Zend\Form\Element\Csrf',
                    'name' => 'security',
                    'options' => array(
                        'csrf_options' => array(
                            'messageTemplates' => array(
                                'notSame' => 'csrf-message'
                            ),
                            'timeout' => 3600
                        )
                    ),
                    'attributes' => array(
                        'class' => 'js-csrf-token'
                    )
                )
            )
            ->shouldReceive('add')
            ->with(
                array(
                    'type' => '\Zend\Form\Element\Button',
                    'name' => 'form-actions[continue]',
                    'options' => array(
                        'label' => 'Continue'
                    ),
                    'attributes' => array(
                        'type' => 'submit',
                        'class' => 'visually-hidden',
                        'id' => 'hidden-continue'
                    )
                )
            );

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        // Mock the auth service to allow form test to pass through uninhibited
        $mockAuthService = m::mock();

        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(false);

        $sm->shouldReceive('get')
            ->once()
            ->with('ZfcRbac\Service\AuthorizationService')
            ->andReturn($mockAuthService);

        $builder = m::mock('\stdClass');

        $sm->shouldReceive('get')
            ->once()
            ->with('FormAnnotationBuilder')
            ->andReturn($builder);

        $builder->shouldReceive('createForm')
            ->once()
            ->with('Common\Form\Model\Form\MyFakeFormTest')
            ->andReturn($form);

        $helper->setServiceLocator($sm);

        $result = $helper->createForm('MyFakeFormTest');

        $this->assertEquals($form, $result);
    }

    public function testCreateFormWithValidFormAndNoCsrfOrContinue()
    {
        $helper = new FormHelperService();

        $form = m::mock('Common\Form\Model\Form\MyFakeFormTest');

        // @NOTE: the below should work according to the docs but it doesn't. However
        // *not* adding any expectations throws an error if methods are then called,
        // so not adding this is the same as asking for add to never be called
        //$form->shouldReceive('add')->never();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        // Mock the auth service to allow form test to pass through uninhibited
        $mockAuthService = m::mock();

        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(false);

        $sm->shouldReceive('get')
            ->once()
            ->with('ZfcRbac\Service\AuthorizationService')
            ->andReturn($mockAuthService);

        $builder = m::mock('\stdClass');

        $sm->shouldReceive('get')
            ->once()
            ->with('FormAnnotationBuilder')
            ->andReturn($builder);

        $builder->shouldReceive('createForm')
            ->once()
            ->with('Common\Form\Model\Form\MyFakeFormTest')
            ->andReturn($form);

        $helper->setServiceLocator($sm);

        $result = $helper->createForm('MyFakeFormTest', false, false);

        $this->assertEquals($form, $result);
    }

    public function testProcessAddressLookupWithNoPostcodeOrAddressSelected()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn([])
            ->shouldReceive('isPost')
            ->andReturn(false);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertFalse(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithAddressSelected()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $addressData = m::mock('\stdClass');
        $addressData->shouldReceive('getAddressForUprn')
            ->with(['address1'])
            ->andReturn('address_1234');

        $addressHelper = m::mock('\stdClass');
        $addressHelper->shouldReceive('formatPostalAddress')
            ->with('address_1234')
            ->andReturn('formatted1');

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andReturn($addressData)
            ->getMock()
            ->shouldReceive('get')
            ->with('Helper\Address')
            ->andReturn($addressHelper);

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'select' => true,
                            'addresses' => ['address1']
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset])
            ->shouldReceive('setData')
            ->with(
                ['address' => 'formatted1']
            );

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessNestedAddressLookupWithAddressSelected()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $addressData = m::mock('\stdClass');
        $addressData->shouldReceive('getAddressForUprn')
            ->with(['address1'])
            ->andReturn('address_1234');

        $addressHelper = m::mock('\stdClass');
        $addressHelper->shouldReceive('formatPostalAddress')
            ->with('address_1234')
            ->andReturn('formatted1');

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andReturn($addressData)
            ->getMock()
            ->shouldReceive('get')
            ->with('Helper\Address')
            ->andReturn($addressHelper);

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'top-level' => [
                        'address' => [
                            'searchPostcode' => [
                                'select' => true,
                                'addresses' => ['address1']
                            ]
                        ],
                        'foo' => 'bar'
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->shouldReceive('remove')
            ->with('select');

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $topFieldset = m::mock('Zend\Form\Fieldset');
        $topFieldset->shouldReceive('getName')
            ->andReturn('top-level')
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$topFieldset])
            ->shouldReceive('setData')
            ->with(
                [
                    'top-level' => [
                        'address' => 'formatted1',
                        'foo' => 'bar'
                    ]
                ]
            );

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithPostcodeSearch()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $address = m::mock('\stdClass');
        $address->shouldReceive('getAddressesForPostcode')
            ->andReturn(['address1', 'address2']);

        $addressHelper = m::mock('\stdClass');
        $addressHelper->shouldReceive('formatAddressesForSelect')
            ->with(['address1', 'address2'])
            ->andReturn(['formatted1', 'formatted2']);

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andReturn($address)
            ->getMock()
            ->shouldReceive('get')
            ->with('Helper\Address')
            ->andReturn($addressHelper);

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock('\stdClass');
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock('\stdClass');
        $element->shouldReceive('get')
            ->with('addresses')
            ->andReturn($addressElement);

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessNestedAddressLookupWithPostcodeSearch()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $address = m::mock('\stdClass');
        $address->shouldReceive('getAddressesForPostcode')
            ->andReturn(['address1', 'address2']);

        $addressHelper = m::mock('\stdClass');
        $addressHelper->shouldReceive('formatAddressesForSelect')
            ->with(['address1', 'address2'])
            ->andReturn(['formatted1', 'formatted2']);

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andReturn($address)
            ->getMock()
            ->shouldReceive('get')
            ->with('Helper\Address')
            ->andReturn($addressHelper);

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'deeply' => [
                        'nested' => [
                            'address' => [
                                'searchPostcode' => [
                                    'search' => true,
                                    'postcode' => 'LSX XXX'
                                ]
                            ],
                            'foo' => 'bar'
                        ],
                        'baz' => true
                    ],
                    'test' => false
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock('\stdClass');
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock('\stdClass');
        $element->shouldReceive('get')
            ->with('addresses')
            ->andReturn($addressElement);

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $topFieldset = m::mock('Zend\Form\Fieldset');
        $topFieldset->shouldReceive('getName')
            ->andReturn('deeply')
            ->shouldReceive('getFieldsets')
            ->andReturn(
                [
                    m::mock('Zend\Form\Fieldset')
                    ->shouldReceive('getName')
                    ->andReturn('nested')
                    ->shouldReceive('getFieldsets')
                    ->andReturn([$fieldset])
                    ->getMock()
                ]
            );

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$topFieldset]);

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithEmptyAddresses()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $address = m::mock('\stdClass');
        $address->shouldReceive('getAddressesForPostcode')
            ->andReturn([]);

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andReturn($address);

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $addressElement = m::mock('\stdClass');
        $addressElement->shouldReceive('setValueOptions')
            ->with(['formatted1', 'formatted2']);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->with(array('postcode.error.no-addresses-found'));

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupWithEmptyPostcodeSearch()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => ''
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->with(array('Please enter a postcode'));

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testProcessAddressLookupServiceUnavailable()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $address = m::mock('\stdClass');
        $address->shouldReceive('getAddressesForPostcode')
            ->andReturn([]);

        $sm->shouldReceive('get')
            ->with('Data\Address')
            ->andThrow(new \Exception('fail'));

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $request = m::mock('Zend\Http\Request');
        $request->shouldReceive('getPost')
            ->andReturn(
                [
                    'address' => [
                        'searchPostcode' => [
                            'search' => true,
                            'postcode' => 'LSX XXX'
                        ]
                    ]
                ]
            )
            ->shouldReceive('isPost')
            ->andReturn(true);

        $element = m::mock('\stdClass');
        $element->shouldReceive('remove')
            ->with('addresses')
            ->getMock()
            ->shouldReceive('remove')
            ->with('select')
            ->getMock()
            ->shouldReceive('setMessages')
            ->once()
            ->with(array('postcode.error.not-available'));

        $fieldset = m::mock('Common\Form\Elements\Types\Address');
        $fieldset->shouldReceive('getName')
            ->andReturn('address')
            ->shouldReceive('get')
            ->with('searchPostcode')
            ->andReturn($element);

        $form->shouldReceive('getFieldsets')
            ->once()
            ->andReturn([$fieldset]);

        $this->assertTrue(
            $helper->processAddressLookupForm($form, $request)
        );
    }

    public function testDisableElementWithNestedSelector()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $validator = m::mock('\stdClass');
        $validator->shouldReceive('setAllowEmpty')
            ->with(true)
            ->shouldReceive('setRequired')
            ->with(false);

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($validator);

        $element = m::mock('\stdClass');
        $element->shouldReceive('setAttribute')
            ->with('disabled', 'disabled');

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $helper->disableElement($form, 'foo->bar');
    }

    public function testDisableElementWithDateInput()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $validator = m::mock('\stdClass');
        $validator->shouldReceive('setAllowEmpty')
            ->with(true)
            ->shouldReceive('setRequired')
            ->with(false);

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('bar')
            ->andReturn($validator);

        $element = m::mock('Zend\Form\Element\DateSelect');

        $subElement = m::mock('\stdClass');
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $element->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('get')
            ->with('bar')
            ->andReturn($element);

        $helper->disableElement($form, 'bar');
    }

    public function testDisableDateElement()
    {
        $helper = new FormHelperService();

        $element = m::mock('Zend\Form\Element\DateSelect');

        $subElement = m::mock('\stdClass');
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $element->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $helper->disableDateElement($element);
    }

    public function testRemove()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter);

        $helper->remove($form, 'foo->bar');
    }

    public function testDisableElements()
    {
        $helper = new FormHelperService();

        $subElement = m::mock('\stdClass');
        $subElement->shouldReceive('setAttribute')
            ->times(3)
            ->with('disabled', 'disabled');

        $dateElement = m::mock('Zend\Form\Element\DateSelect');
        $dateElement->shouldReceive('getDayElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getMonthElement')
            ->andReturn($subElement)
            ->getMock()
            ->shouldReceive('getYearElement')
            ->andReturn($subElement);

        $element = m::mock('Zend\Form\Element');
        $element->shouldReceive('setAttribute')
            ->with('disabled', 'disabled');

        $form = m::mock('Zend\Form\Form');

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset->shouldReceive('getElements')
            ->andReturn([$dateElement])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([]);

        $form->shouldReceive('getElements')
            ->andReturn([$element])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        $helper->disableElements($form);
    }

    public function testDisableValidation()
    {
        $helper = new FormHelperService();

        $input = m::mock('Zend\InputFilter\Input');
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->getMock()
            ->shouldReceive('setRequired')
            ->with(false)
            ->getMock()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('getInputs')
            ->andReturn([$input]);

        $helper->disableValidation($filter);
    }

    public function testDisableEmptyValidation()
    {
        $helper = new FormHelperService();

        $input = m::mock('Zend\InputFilter\Input');
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('setRequired')
            ->with(false)
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturn($input)
            ->getMock()
            ->shouldReceive('has')
            ->andReturn(true)
            ->once()
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturnSelf();

        $element = m::mock('\stdClass');
        $element->shouldReceive('getValue')
            ->andReturn('');

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset->shouldReceive('getName')
            ->andReturn('fieldset')
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('getElements')
            ->andReturn([]);

        $form = m::mock('Zend\Form\Form');
        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->getMock()
            ->shouldReceive('getElements')
            ->andReturn(['foo' => $element])
            ->getMock()
            ->shouldReceive('getFieldsets')
            ->andReturn([$fieldset]);

        $helper->disableEmptyValidation($form);
    }

    public function testDisableEmptyValidationOnElement()
    {
        $helper = new FormHelperService();

        $input = m::mock('Zend\InputFilter\Input');
        $input->shouldReceive('setAllowEmpty')
            ->with(true)
            ->andReturnSelf()
            ->shouldReceive('setRequired')
            ->with(false)
            ->andReturnSelf()
            ->shouldReceive('setValidatorChain');

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturn($input)
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturnSelf();

        $element = m::mock('\stdClass');

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset
            ->shouldReceive('get')
            ->with('foo')
            ->andReturn($element);

        $form = m::mock('Zend\Form\Form');
        $form->shouldReceive('getInputFilter')
            ->andReturn($filter)
            ->shouldReceive('get')
            ->with('fieldset')
            ->andReturn($fieldset);

        $helper->disableEmptyValidationOnElement($form, 'fieldset->foo');
    }

    public function testPopulateFormTable()
    {
        $helper = new FormHelperService();

        $table = m::mock('Common\Service\Table\TableBuilder');
        $table->shouldReceive('getRows')
            ->andReturn([1, 2, 3, 4]);

        $tableInput = m::mock('\stdClass');
        $tableInput->shouldReceive('setTable')
            ->with($table, 'fieldset');

        $rowInput = m::mock('\stdClass');
        $rowInput->shouldReceive('setValue')
            ->with(4);

        $fieldset = m::mock('Zend\Form\Fieldset');
        $fieldset->shouldReceive('get')
            ->with('table')
            ->andReturn($tableInput)
            ->getMock()
            ->shouldReceive('get')
            ->with('rows')
            ->andReturn($rowInput);

        $helper->populateFormTable($fieldset, $table, 'fieldset');
    }

    public function testLockElement()
    {
        $helper = new FormHelperService();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $translator = m::mock('\stdClass');
        $translator->shouldReceive('translate')
            ->with('message')
            ->andReturn('translated')
            ->shouldReceive('translate')
            ->with('label')
            ->andReturn('label');

        $renderer = m::mock('\stdClass');
        $renderer->shouldReceive('render')
            ->andReturn('template');

        $sm->shouldReceive('get')
            ->once()
            ->with('ViewRenderer')
            ->andReturn($renderer)
            ->getMock()
            ->shouldReceive('get')
            ->with('Helper\Translation')
            ->andReturn($translator);

        $element = m::mock('Zend\Form\Element');
        $element->shouldReceive('getLabel')
            ->andReturn('label')
            ->getMock()
            ->shouldReceive('setLabel')
            ->with('labeltemplate')
            ->getMock()
            ->shouldReceive('setLabelOption')
            ->with('disable_html_escape', true)
            ->getMock()
            ->shouldReceive('getLabelAttributes')
            ->andReturn(['foo' => 'bar'])
            ->getMock()
            ->shouldReceive('setLabelAttributes')
            ->with(
                [
                    'foo' => 'bar',
                    'class' => ''
                ]
            );

        $helper->setServiceLocator($sm);

        $helper->lockElement($element, 'message');
    }

    public function testRemoveFieldLiset()
    {
        $helper = new FormHelperService();

        $form = m::mock('Zend\Form\Form');

        $form->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $filter = m::mock('Zend\InputFilter\InputFilter');
        $filter->shouldReceive('get')
            ->with('foo')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('remove')
            ->with('bar');

        $form->shouldReceive('getInputFilter')
            ->andReturn($filter);

        $helper->removeFieldList($form, 'foo', ['bar']);
    }

    public function testProcessCompanyLookupValidData()
    {
        $helper = new FormHelperService();

        $service = m::mock()
            ->shouldReceive('search')
            ->with('companyDetails', '12345678')
            ->andReturn(
                [
                    'Count' => 1,
                    'Results' => [
                        [
                            'CompanyName' => 'Looked Up Company',
                            'RegAddress' => [
                                'AddressLine' => [
                                    'MILLENNIUM STADIUM',
                                    'WESTGATE STREET',
                                    'CARDIFF',
                                    'CF10 1NS',
                                ],
                            ],
                        ],
                    ],
                ]
            )
            ->getMock();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->once()
            ->with('Data\CompaniesHouse')
            ->andReturn($service)
            ->getMock();

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $data = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '12345678'
                ]
            ]
        ];

        $nameElement = m::mock()->shouldReceive('setValue')
            ->with('Looked Up Company')
            ->getMock();

        $fieldset = m::mock()->shouldReceive('get')
            ->with('name')
            ->andReturn($nameElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        $addressFieldset = m::mock()
            ->shouldReceive('get')
            ->with('postcode')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('CF10 1NS')->getMock()
            )
            ->shouldReceive('get')
            ->with('addressLine1')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('MILLENNIUM STADIUM')->getMock()
            )
            ->shouldReceive('get')
            ->with('addressLine2')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('WESTGATE STREET')->getMock()
            )
            ->shouldReceive('get')
            ->with('addressLine3')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('CARDIFF')->getMock()
            )
            ->shouldReceive('get')
            ->with('addressLine4')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('')->getMock()
            )
            ->shouldReceive('get')
            ->with('town')
            ->andReturn(
                m::mock()->shouldReceive('setValue')->with('')->getMock()
            )
            ->getMock();

        $form->shouldReceive('get')
            ->with('registeredAddress')
            ->andReturn($addressFieldset);

        $helper->processCompanyNumberLookupForm($form, $data, 'data', 'registeredAddress');
    }

    /**
     * @dataProvider companyNumberProvider
     */
    public function testProcessCompanyLookupWithNoResults($firstNumber, $secondNumber)
    {
        $helper = new FormHelperService();

        $service = m::mock()
            ->shouldReceive('search')
            ->with('companyDetails', $firstNumber)
            ->andReturn(
                [
                    'Count' => 0,
                    'Results' => []
                ]
            )
            ->once()
            ->shouldReceive('search')
            ->with('companyDetails', $secondNumber)
            ->andReturn(
                [
                    'Count' => 0,
                    'Results' => []
                ]
            )
            ->once()
            ->getMock();

        $translator = m::mock()
            ->shouldReceive('translate')
            ->with('company_number.search_no_results.error')
            ->andReturn('No results')
            ->getMock();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('Data\CompaniesHouse')
            ->andReturn($service)
            ->shouldReceive('get')
            ->with('translator')
            ->andReturn($translator)
            ->getMock();

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $data = [
            'data' => [
                'companyNumber' => [
                    'company_number' => $firstNumber
                ]
            ]
        ];

        $numberElement = m::mock()->shouldReceive('setMessages')
            ->with(
                [
                    'company_number' => ['No results']
                ]
            )
            ->getMock();

        $fieldset = m::mock()->shouldReceive('get')
            ->with('companyNumber')
            ->andReturn($numberElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        $helper->processCompanyNumberLookupForm($form, $data, 'data');
    }

    public function companyNumberProvider()
    {
        return [
            ['01234567', '1234567'],
            ['1234567', '01234567'],
        ];
    }

    public function testProcessCompanyLookupInvalidNumber()
    {
        $helper = new FormHelperService();

        $translator = m::mock()
            ->shouldReceive('translate')
            ->with('company_number.length.validation.error')
            ->andReturn('Bad length')
            ->getMock();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('translator')
            ->andReturn($translator)
            ->getMock();

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $data = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '123456789'
                ]
            ]
        ];

        $numberElement = m::mock()->shouldReceive('setMessages')
            ->with(
                [
                    'company_number' => ['Bad length']
                ]
            )
            ->getMock();

        $fieldset = m::mock()->shouldReceive('get')
            ->with('companyNumber')
            ->andReturn($numberElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        $helper->processCompanyNumberLookupForm($form, $data, 'data');
    }

    public function testProcessCompanyLookupError()
    {
        $helper = new FormHelperService();

        $service = m::mock()
            ->shouldReceive('search')
            ->with('companyDetails', '12345678')
            ->andThrow(new \Exception('xml gateway error'))
            ->getMock();

        $translator = m::mock()
            ->shouldReceive('translate')
            ->with('company_number.search_error.error')
            ->andReturn('API error')
            ->getMock();

        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('Data\CompaniesHouse')
            ->andReturn($service)
            ->shouldReceive('get')
            ->with('translator')
            ->andReturn($translator)
            ->getMock();

        $helper->setServiceLocator($sm);

        $form = m::mock('Zend\Form\Form');

        $data = [
            'data' => [
                'companyNumber' => [
                    'company_number' => '12345678'
                ]
            ]
        ];

        $numberElement = m::mock()->shouldReceive('setMessages')
            ->with(
                [
                    'company_number' => ['API error']
                ]
            )
            ->getMock();

        $fieldset = m::mock()->shouldReceive('get')
            ->with('companyNumber')
            ->andReturn($numberElement)
            ->getMock();

        $form->shouldReceive('get')
            ->with('data')
            ->andReturn($fieldset);

        $helper->processCompanyNumberLookupForm($form, $data, 'data');
    }

    public function testSetFormActionFromRequestWhenFormHasAction()
    {
        $helper = new FormHelperService();

        $form = m::mock()
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(true)
            ->getMock();

        $request = m::mock()
            ->shouldReceive('getUri')->never()
            ->getMock();

        $helper->setFormActionFromRequest($form, $request);
    }

    public function testSetFormActionFromRequest()
    {
        $helper = new FormHelperService();

        $form = m::mock()
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(false)
            ->shouldReceive('setAttribute')
            ->with('action', 'URI?QUERY')
            ->getMock();

        $request = m::mock();

        $request->shouldReceive('getUri->getPath')
            ->andReturn('URI');

        $request->shouldReceive('getUri->getQuery')
            ->andReturn('QUERY');

        $helper->setFormActionFromRequest($form, $request);
    }

    public function testSetFormActionFromRequestWithNoQuery()
    {
        $helper = new FormHelperService();

        $form = m::mock()
            ->shouldReceive('getAttribute')
            ->with('method')
            ->andReturn('POST')
            ->shouldReceive('hasAttribute')
            ->with('action')
            ->andReturn(false)
            ->shouldReceive('setAttribute')
            ->with('action', 'URI/ ')
            ->getMock();

        $request = m::mock();

        $request->shouldReceive('getUri->getPath')
            ->andReturn('URI/');

        $request->shouldReceive('getUri->getQuery')
            ->andReturn('');

        $helper->setFormActionFromRequest($form, $request);
    }

    public function testRemoveOptionWithoutOption()
    {
        $helper = new FormHelperService();

        $index = 'blap';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options);

        $helper->removeOption($element, $index);
    }

    public function testRemoveOptionWithOption()
    {
        $helper = new FormHelperService();

        $index = 'foo';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options)
            ->shouldReceive('setValueOptions')
            ->with(['bar' => 'baz']);

        $helper->removeOption($element, $index);
    }

    public function testSetCurrentOptionWithoutCurrentOption()
    {
        $helper = new FormHelperService();

        $index = 'blap';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options);

        $helper->setCurrentOption($element, $index);
    }

    public function testSetCurrentOptionWithCurrentOption()
    {
        $sm = \CommonTest\Bootstrap::getServiceManager();

        $helper = new FormHelperService();
        $helper->setServiceLocator($sm);

        $mockTranslator = m::mock();
        $mockTranslator->shouldReceive('translate')
            ->with('current.option.suffix')
            ->andReturn('(current)');
        $mockTranslator->shouldReceive('translate')
            ->with('baz')
            ->andReturn('baz-translated');

        $sm->setService('Helper\Translation', $mockTranslator);

        $index = 'bar';

        $options = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        /** @var Select|\Mockery\MockInterface $element */
        $element = m::mock(Select::class);
        $element->shouldReceive('getValueOptions')
            ->andReturn($options)
            ->shouldReceive('setValueOptions')
            ->with(['foo' => 'bar', 'bar' => 'baz-translated (current)']);

        $helper->setCurrentOption($element, $index);
    }

    public function testCreateFormWithRequest()
    {
        // since the method we're testing just composes two other public ones, making
        // a partial mock is fine
        $helper = m::mock('Common\Service\Helper\FormHelperService')->makePartial();

        $form = m::mock();

        $helper->shouldReceive('createForm')
            ->with('MyForm')
            ->andReturn($form)
            ->shouldReceive('setFormActionFromRequest')
            ->with($form, 'request');

        $this->assertEquals(
            $form,
            $helper->createFormWithRequest('MyForm', 'request')
        );
    }

    public function testGetValidator()
    {
        $validatorName = '\Zend\Validator\GreaterThan';

        $helper    = new FormHelperService();
        $form      = m::mock('Zend\Form\Form');
        $validator = m::mock($validatorName);
        $element   = m::mock();
        $filter    = m::mock('\Zend\InputFilter\InputFilter');

        $form->shouldReceive('getInputFilter')->andReturn($filter);

        $filter->shouldReceive('get')->with('myelement')->andReturn($element);

        $element->shouldReceive('getValidatorChain')->andReturn(
            m::mock()
                ->shouldReceive('getValidators')
                ->andReturn(
                    [
                        ['instance' => $validator],
                        ['instance' => m::mock()],
                    ]
                )
                ->getMock()
        );

        $result = $helper->getValidator($form, 'myelement', $validatorName);

        $this->assertSame($validator, $result);
    }

    public function testGetValidatorNotFoundReturnsNull()
    {
        $helper    = new FormHelperService();
        $form      = m::mock('Zend\Form\Form');
        $element   = m::mock();
        $filter    = m::mock('\Zend\InputFilter\InputFilter');

        $form->shouldReceive('getInputFilter')->andReturn($filter);

        $filter->shouldReceive('get')->with('myelement')->andReturn($element);

        $element->shouldReceive('getValidatorChain')->andReturn(
            m::mock()
                ->shouldReceive('getValidators')
                ->andReturn([])
                ->getMock()
        );

        $this->assertNull($helper->getValidator($form, 'myelement', 'MyValidator'));
    }

    public function testAttachValidator()
    {
        /** @var FormInterface|\Mockery\MockInterface $mockForm */
        $mockForm = m::mock(FormInterface::class);
        /** @var InputFilterInterface|\Mockery\MockInterface $mockForm */
        $mockInputFilter = m::mock(InputFilterInterface::class);
        $mockValidator = m::mock();
        $mockValidatorChain = m::mock();

        $mockForm->shouldReceive('getInputFilter')
            ->once()
            ->andReturn($mockInputFilter)
            ->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturnSelf();

        $mockInputFilter->shouldReceive('get')
            ->once()
            ->with('data')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturnSelf()
            ->shouldReceive('getValidatorChain')
            ->andReturn($mockValidatorChain);

        $mockValidatorChain->shouldReceive('attach')
            ->once()
            ->with($mockValidator);

        $helper = new FormHelperService();
        $helper->attachValidator($mockForm, 'data->foo', $mockValidator);
    }

    public function testSetDefaultDate()
    {
        $helper = new FormHelperService();
        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $helper->setServiceLocator($sm);

        // mocks
        $field      = m::mock();
        $dateHelper = m::mock();
        $today      = m::mock('\DateTime');

        // expectations
        $sm->shouldReceive('get')->with('Helper\Date')->andReturn($dateHelper);
        $field->shouldReceive('getValue')->andReturn('--');
        $dateHelper->shouldReceive('getDateObject')->andReturn($today);
        $field->shouldReceive('setValue')->with($today);

        $helper->setDefaultDate($field);
    }

    public function testSetDefaultDateFieldAlreadyHasValue()
    {
        $helper = new FormHelperService();
        $sm = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $helper->setServiceLocator($sm);

        // mocks
        $field      = m::mock();

        // expectations
        $sm->shouldReceive('get')->with('Helper\Date')->never();
        $field->shouldReceive('getValue')->andReturn('2015-04-09');
        $field->shouldReceive('setValue')->never();

        $helper->setDefaultDate($field);
    }

    public function testSaveFormState()
    {
        $helper = new FormHelperService();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('getName')->with()->once()->andReturn('FORM_NAME');

        $helper->saveFormState($mockForm, ['foo' => 'bar']);

        $sessionContainer = new \Zend\Session\Container('form_state');
        $this->assertEquals(['foo' => 'bar'], $sessionContainer->offsetGet('FORM_NAME'));
    }

    public function testRestoreFormState()
    {
        $helper = new FormHelperService();

        $mockForm = m::mock('Zend\Form\Form');
        $mockForm->shouldReceive('getName')->with()->twice()->andReturn('FORM_NAME');

        $sessionContainer = new \Zend\Session\Container('form_state');
        $sessionContainer->offsetSet('FORM_NAME', ['an' => 'array']);
        $mockForm->shouldReceive('setData')->with(['an' => 'array'])->once();

        $helper->restoreFormState($mockForm);
    }

    public function testRemoveValueOption()
    {
        $helper = new FormHelperService();

        $options = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C'
        ];

        /** @var Select $select */
        $select = m::mock(Select::class)->makePartial();
        $select->setValueOptions($options);

        $helper->removeValueOption($select, 'a');

        $this->assertEquals(['b' => 'B', 'c' => 'C'], $select->getValueOptions());
    }
}
