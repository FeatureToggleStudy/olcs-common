<?php
namespace Common\Form\Elements\InputFilters;

use Zend\InputFilter\InputProviderInterface as InputProviderInterface;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Custom Textarea element with required parameter
 */
class TextareaRequired extends Textarea implements InputProviderInterface
{
    protected $required = true;
}
