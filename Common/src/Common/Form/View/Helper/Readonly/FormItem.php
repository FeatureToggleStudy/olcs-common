<?php

namespace Common\Form\View\Helper\Readonly;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;
use Common\Form\Elements;

/**
 * Class FormItem
 * @package Common\Form\View\Helper\Readonly
 */
class FormItem extends AbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface|null $element Element
     *
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render
     *
     * @param ElementInterface $element Element
     *
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (
            $element instanceof Elements\InputFilters\ActionButton
            || $element instanceof Elements\Types\AttachFilesButton
            || $element instanceof \Zend\Form\Element\Submit
            || $element instanceof \Zend\Form\Element\Hidden
            || $element instanceof \Zend\Form\Element\Button
        ) {
            return '';
        }

        if ($element->getOption('disable_html_escape')) {
            return $element->getValue();
        }

        $escapeHelper = $this->getEscapeHtmlHelper();
        return $escapeHelper($element->getValue());
    }
}
