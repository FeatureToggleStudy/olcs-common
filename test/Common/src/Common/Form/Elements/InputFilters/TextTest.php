<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;

/**
 * @covers \Common\Form\Elements\InputFilters\Text
 */
class TextTest extends \PHPUnit\Framework\TestCase
{
    /** @var  InputFilters\Text */
    private $filter;

    /**
     * test setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->filter = new InputFilters\Text("test");
    }

    /**
     * helper to extract a key out of the specification array
     *
     * @param string $key key to extract
     *
     * @return mixed
     */
    protected function getSpecificationElement($key)
    {
        return $this->filter->getInputSpecification()[$key];
    }

    /**
     * test basic name
     *
     * @return void
     */
    public function testGetInputSpecificationReturnsCorrectName()
    {
        $this->assertEquals('test', $this->getSpecificationElement('name'));
    }

    /**
     * ensure text fields aren't required by default
     *
     * @return void
     */
    public function testTextNotRequired()
    {
        $this->assertFalse($this->getSpecificationElement('required'));
    }

    /**
     * ensure we trim all input strings
     *
     * @return void
     */
    public function testStringTrimFilterIsUsed()
    {
        $this->assertEquals(
            [['name' => 'Zend\Filter\StringTrim']],
            $this->getSpecificationElement('filters')
        );
    }

    /**
     * Test set max
     *
     * @return void
     */
    public function testSetMinMax()
    {
        $this->filter
            ->setMin(99)
            ->setMax(888);

        $validators = $this->getSpecificationElement('validators');
        $options = current($validators)['options'];

        static::assertEquals(99, $options['min']);
        static::assertEquals(888, $options['max']);
    }

    /**
     * Test set max
     *
     * @return void
     */
    public function testIsAllowEmpty()
    {
        $this->filter
            ->setMin(0)
            ->setMax(0)
            ->setAllowEmpty(true);

        $validators = $this->getSpecificationElement('validators');

        static::assertEquals(
            [
                'name' => \Zend\Validator\NotEmpty::class,
                'options' => [
                    'type' => \Zend\Validator\NotEmpty::PHP,
                ],
            ],
            current($validators)
        );
    }
}
