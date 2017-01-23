<?php
namespace Common\Service\Table\Type;

/**
 * Action type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Action extends AbstractType
{
    /**
     * Holds the format
     *
     * @var string
     */
    private $format = '<input type="submit" class="%s" name="%s" value="%s" %s />';

    /**
     * Render the selector
     *
     * @param array $data
     * @param array $column
     * @return string
     */
    public function render($data, $column, $formattedContent = null)
    {
        $class = isset($column['class']) ? $column['class'] : '';

        if ($formattedContent !== null) {
            $value = $formattedContent;
        } elseif (isset($column['text'])) {
            $value = $column['text'];
        } elseif (isset($column['value_format'])) {
            $value = $this->getTable()->replaceContent($column['value_format'], $data);
        } else {
            $value = (isset($column['name']) && isset($data[$column['name']]) ? $data[$column['name']] : '');
        }

        $name = 'action';

        $fieldset = $this->getTable()->getFieldset();
        if (!empty($fieldset)) {
            $name = $fieldset . '[action]';
        }

        $name .= '[' . $column['action'] . '][' . $data['id'] . ']';

        $attributes = isset($column['action-attributes']) ? $column['action-attributes'] : [];

        if (isset($column['keepForReadOnly']) && $column['keepForReadOnly']) {
            return $value;
        }

        return sprintf($this->format, $class, $name, $value, implode(' ', $attributes));
    }
}
