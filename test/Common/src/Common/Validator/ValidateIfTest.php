<?php

namespace CommonTest\Validator;

use Common\Validator\ValidateIf;
use Mockery as m;

class ValidateIfTest extends \PHPUnit_Framework_TestCase
{
    public function testSetOptions()
    {
        $sut = new ValidateIf();
        $sut->setOptions(['context_field' =>'test', 'context_truth' => false, 'context_values' => [null]]);

        $this->assertEquals('test', $sut->getContextField());
        $this->assertEquals([null], $sut->getContextValues());
        $this->assertEquals(false, $sut->getContextTruth());
    }

    public function testGetValidatorChain()
    {
        $mockValidator = m::mock('Zend\Validator\NotEmpty');

        $mockValidatorPluginManager = m::mock('Zend\Validator\ValidatorPluginManager');
        $mockValidatorPluginManager->shouldReceive('get')->with('NotEmpty', [])->andReturn($mockValidator);

        $sut = new ValidateIf();
        $sut->setValidatorPluginManager($mockValidatorPluginManager);
        $sut->setValidators([['name'=>'NotEmpty']]);

        $validatorChain = $sut->getValidatorChain();
        $this->assertInstanceOf('Zend\Validator\ValidatorChain', $validatorChain);
        $this->assertSame($validatorChain, $sut->getValidatorChain());

        $this->assertCount(1, $validatorChain->getValidators());
        $this->assertSame($mockValidatorPluginManager, $validatorChain->getPluginManager());
    }

    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $options
     * @param $context
     * @param $chainValid
     * @param array $errorMessages
     */
    public function testIsValid($expected, $options, $context, $chainValid, $errorMessages=[])
    {
        $value = 'isValid';
        $errorMessages = empty($errorMessages) ? ['error' => 'message'] : $errorMessages;

        $mockValidatorChain = m::mock('Zend\Validator\ValidatorChain');
        $mockValidatorChain->shouldReceive('isValid')->with($value, $context)->andReturn($chainValid);
        $mockValidatorChain->shouldReceive('getMessages')->andReturn($errorMessages);

        $sut = new ValidateIf();
        $sut->setValidatorChain($mockValidatorChain);
        $sut->setOptions($options);
        $this->assertEquals($expected, $sut->isValid($value, $context));

        if (!$expected) {
            $this->assertEquals($errorMessages, $sut->getMessages());
        }
    }

    public function provideIsValid()
    {
        return [
            //context matches, field is valid
            [true, ['context_field' => 'field', 'context_values' => ['Y']], ['field'=>'Y'], true],
            //context matches, field is invalid
            [false, ['context_field' => 'field', 'context_values' => ['Y']], ['field'=>'Y'], false],
            //context doesn't match, field is invalid
            [true, ['context_field' => 'field', 'context_values' => ['Y']], ['field'=>'N'], false],
            //inverse context match, field valid
            [true, ['context_field' => 'field', 'context_values' => ['Y'], 'context_truth' => 0], ['field'=>'N'], true],
            //missing context
            [false, [], [], false, ['no_context' => 'Context field was not found in the input']]

        ];
    }
}
 