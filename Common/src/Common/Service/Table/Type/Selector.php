<?php

/**
 * Selector type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Type;

/**
 * Selector type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Selector extends AbstractType
{
    protected $format = '<input type="radio" name="%s" value="%s" %s />';

    /**
     * Render the selector
     *
     * @param array $data
     * @param array $column
     * @param string $formattedContent
     *
     * @return string
     */
    public function render($data, $column, $formattedContent = null)
    {
        $fieldset = $this->getTable()->getFieldset();

        $name = 'id';

        if (!empty($fieldset)) {
            $name = $fieldset . '[id]';
        }

        $dataAttributes = [];

        if (isset($column['data-attributes'])) {
            foreach ($column['data-attributes'] as $attrName) {
                if (isset($data[$attrName])) {
                    $dataAttributes[] = 'data-' . $attrName . '="' . $data[$attrName] . '"';
                }
            }
        }

        return sprintf($this->format, $name, $data['id'], implode(' ', $dataAttributes));
    }
}
