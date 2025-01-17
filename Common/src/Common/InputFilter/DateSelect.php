<?php

namespace Common\InputFilter;

use Zend\InputFilter\Input as ZendInput;

/**
 * Class DateSelect
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DateSelect extends ZendInput
{
    /**
     * @return mixed
     */
    public function getRawValue()
    {
        // if all elements of the date are empty then return null
        if (
            is_array($this->value) &&
            empty($this->value['day']) &&
            empty($this->value['month']) &&
            empty($this->value['year'])
        ) {
            return null;
        }

        return $this->value;
    }
}
