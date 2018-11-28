<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;
use \Zend\Validator\StringLength;

/**
 * Test MultiCheckboxempty InputFilter
 * @covers \Common\Form\Elements\InputFilters\MultiCheckboxEmpty
 */
class MultiCheckboxEmptyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->filter = new InputFilters\MultiCheckboxEmpty("test");
    }

    /**
     * helper to extract a key out of the specification array
     *
     * @param string $key key to extract
     *
     * @return array
     */
    protected function getSpecificationElement($key)
    {
        return $this->filter->getInputSpecification()[$key];
    }

    /**
     * ensure select option is not required by default
     *
     * @return void
     */
    public function testValueNotRequired()
    {
        $this->assertFalse($this->getSpecificationElement('required'));
    }
}
