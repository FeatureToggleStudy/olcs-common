<?php

namespace Common\Form\View\Helper\Readonly;

use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\EscapeHtml;

/**
 * Class FormSelect
 * @package Common\Form\View\Helper\Readonly
 */
class FormSelect extends AbstractHelper
{
    /**
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * Retrieve the escapeHtml helper
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (!$this->escapeHtmlHelper instanceof EscapeHtml) {
            $this->escapeHtmlHelper = new EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface|null $element the element
     *
     * @return string|FormElement
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Process options
     *
     * @param array $input the input
     *
     * @return array
     */
    public function processOptions($input)
    {
        $return = [];

        foreach ($input as $key => $options) {
            if (is_array($options)) {
                if (isset($options['options'])) {
                    $return += $this->processOptions($options['options']);
                } else {
                    $return[$options['value']] = $options['label'];
                }
            } else {
                $return[$key] = $options;
            }
        }

        return $return;
    }

    /**
     * Renders the element
     *
     * @param ElementInterface $element the element
     *
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!($element instanceof Select) && !($element instanceof \Zend\Form\Element\Radio)) {
            return '';
        }

        $valueOptions = $this->processOptions($element->getValueOptions());

        $elementValue = $element->getValue();
        $value = '';
        if (!empty($elementValue)) {
            if ($element->getAttribute('multiple')) {
                $labels = array_intersect_key($valueOptions, array_combine($elementValue, $elementValue));
                $value = implode(', ', $labels);
            } elseif (!empty($valueOptions[$elementValue])) {
                $value = $valueOptions[$elementValue];
            }
        }

        $escapeHelper = $this->getEscapeHtmlHelper();

        return $escapeHelper($value);
    }
}
