<?php

namespace Common\Form\Elements\Custom;

use Zend\Form\Element as ZendElement;

/**
 * DateSelect
 *
 * @author Someone <someone@valtech.co.uk>
 */
class DateSelect extends ZendElement\DateSelect
{
    use Traits\YearDelta {
        setOptions as trait_setOptions;
    }

    /**
     * Set Options
     *
     * @param array|\Traversable $options Options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        if (!isset($options['hint'])) {
            $options['hint'] = 'date-hint';
        }

        $this->trait_setOptions($options);

        return $this;
    }

    /**
     * Get Input Specification
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return array(
            'type' => \Common\InputFilter\DateSelect::class,
            'name' => $this->getName(),
            'required' => $this->getOption('required'),
            'filters' => array(
                array(
                    'name'    => 'Callback',
                    'options' => array(
                        'callback' => function ($date) {
                            // Convert the date to a specific format
                            if (!is_array($date) || empty($date['year']) ||
                                empty($date['month']) || empty($date['day'])) {
                                return null;
                            }

                            return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                        }
                    )
                )
            ),
            'validators' => array(
                $this->getValidator(),
            )
        );
    }
}
