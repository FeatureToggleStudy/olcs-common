<?php

namespace Common\Form\Element;

use Zend\Form\Element\Radio;

/**
 * Class ErrorOverrideRadio
 * @package Common\Form\Element
 */
class ErrorOverrideRadio extends Radio
{
    const INPUT_CLASS_KEY = 'input_class';

    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();

        if (isset($this->options[self::INPUT_CLASS_KEY])) {
            $spec['type'] = $this->options[self::INPUT_CLASS_KEY];
        }

        return $spec;
    }
}
