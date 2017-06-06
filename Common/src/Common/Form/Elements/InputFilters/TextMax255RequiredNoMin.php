<?php
namespace Common\Form\Elements\InputFilters;

use Zend\InputFilter\InputProviderInterface as InputProviderInterface;
use Zend\Validator as ZendValidator;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * Text Max 255 Required no minimum chars
 */
class TextMax255RequiredNoMin extends TextMax255 implements InputProviderInterface
{
    protected $required = true;
    protected $allowEmpty = false;

    /**
     * Provide default input rules for this element.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $specification = [
            'name' => $this->getName(),
            'required' => $this->required,
            'continue_if_empty' => $this->continueIfEmpty,
            'allow_empty' => $this->allowEmpty,
            'filters' => [
                ['name' => 'Zend\Filter\StringTrim']
            ],
            'validators' => $this->getValidators()
        ];

        if (!empty($this->max)) {
            $specification['validators'][] = [
                'name' => 'Zend\Validator\StringLength',
                'options' => ['max' => $this->max]
            ];
        }

        return $specification;
    }
}
