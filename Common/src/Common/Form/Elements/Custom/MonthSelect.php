<?php

/**
 * Month Select
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Form\Elements\Custom;

use Zend\Form\Element as ZendElement;

/**
 * Month Select
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MonthSelect extends ZendElement\MonthSelect
{
    use Traits\YearDelta;

    public function getInputSpecification()
    {
        return array(
            'name' => $this->getName(),
            'required' => $this->getOption('required'),
            'filters' => array(
                array(
                    'name'    => 'Callback',
                    'options' => array(
                        'callback' => function ($date) {
                            // Convert the date to a specific format
                            if (!is_array($date) || empty($date['year']) || empty($date['month'])) {
                                return null;
                            }

                            return $date['year'] . '-' . $date['month'];
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
