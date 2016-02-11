<?php

/**
 * Form Collection wrapper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Element\Collection as CollectionElement;
use Zend\Form\FieldsetInterface;
use Zend\Form\View\Helper\FormCollection as ZendFormCollection;
use Common\Form\Elements\Types\PostcodeSearch;
use Common\Form\Elements\Types\CompanyNumber;
use Common\Form\Elements\Types\FileUploadList;
use Common\Form\Elements\Types\FileUploadListItem;
use Zend\Form\LabelAwareInterface;
use Common\Form\Elements\Types\HoursPerWeek;

/**
 * Form Collection wrapper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormCollection extends \Common\Form\View\Helper\Extended\FormCollection
{
    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * Hint format
     *
     * @var string
     */
    private $hintFormat = "<p class=\"hint\">%s</p>";


    /**
     * @param boolean $readOnly
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Render a collection by iterating through all fieldsets and elements
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $messages = $element->getMessages();

        if ($element instanceof HoursPerWeek) {

            if (isset($messages['hoursPerWeekContent'])) {

                $tmpMessages = [];
                foreach ($messages['hoursPerWeekContent'] as $field => $fieldMessages) {
                    foreach ($fieldMessages as $fieldMessage) {
                        $tmpMessages[] = $fieldMessage;
                    }
                }
                unset($messages['hoursPerWeekContent']);

                $messages = array_merge($messages, $tmpMessages);
            }
        }

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        $hint = $element->getOption('hint');

        if (!empty($hint)) {
            $view = $this->getView();
            $hint = sprintf($this->hintFormat, $view->translate($hint));
        }

        $attributes       = $element->getAttributes();
        $markup           = '';
        $templateMarkup   = '';
        $readOnly = $this->isReadOnly() || $element->getOption('readonly');

        $elementHelper    = (
            $readOnly ?
            $this->getView()->plugin('readonlyformrow') :
            $this->getElementHelper()
        );

        // hide readonly elements with additional option remove_if_readonly
        // e.g. HtmlTranslatable elements where we don't know whether to hide or not
        if ($readOnly && $element->getOption('remove_if_readonly')) {
            return '';
        }

        $fieldsetHelper   = $this->getFieldsetHelper();

        if ($element instanceof CollectionElement && $element->shouldCreateTemplate()) {
            $templateMarkup = $this->renderTemplate($element);
        }

        if ($element instanceof CompanyNumber && !empty($messages)) {
            $attributes['class'] = '';
        }

        foreach ($element->getIterator() as $elementOrFieldset) {
            if ($elementOrFieldset instanceof FieldsetInterface) {
                $markup .= $fieldsetHelper($elementOrFieldset);
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $markup .= $elementHelper($elementOrFieldset);
            }
        }

        if ($readOnly) {
            if ($markup == '') {
                return '';
            }
            return '<ul class="definition-list">' . $markup . '</ul>';
        }

        // If $templateMarkup is not empty, use it for simplify adding new element in JavaScript
        if (!empty($templateMarkup)) {
            $markup .= $templateMarkup;
        }

        // Every collection is wrapped by a fieldset if needed
        if ($this->shouldWrap) {
            $label = $element->getLabel();
            $legend = '';

            if (!empty($label)) {

                if (null !== ($translator = $this->getTranslator())) {
                    $label = $translator->translate(
                        $label,
                        $this->getTranslatorTextDomain()
                    );
                }

                if (! $element instanceof LabelAwareInterface || ! $element->getLabelOption('disable_html_escape')) {
                    $escapeHtmlHelper = $this->getEscapeHtmlHelper();
                    $label = $escapeHtmlHelper($label);
                }

                $legendAttributesString = $this->createAttributesString($element->getLabelAttributes());

                if (!empty($legendAttributesString)) {
                    $legendAttributesString = ' ' . $legendAttributesString;
                }

                $legend = sprintf(
                    '<legend%s>%s</legend>',
                    $legendAttributesString,
                    $label
                );
            }

            // it's helpful from a JS perspective to give our containers
            // (usually fieldsets) an attribute so we can latch on to them
            // explicitally rather than sniffing around in the DOM
            if (($fieldsetName = $element->getName()) !== null) {
                $attributes['data-group'] = $fieldsetName;
            }

            $attributesString = $this->createAttributesString($attributes);
            if (!empty($attributesString)) {
                $attributesString = ' ' . $attributesString;
            }

            if ($element instanceof FileUploadList) {

                $markup = sprintf('<ul%s>%s%s</ul>', $attributesString, $hint, $markup);

            } elseif ($element instanceof FileUploadListItem) {

                $markup = sprintf('<li%s>%s%s</li>', $attributesString, $hint, $markup);
            } else {

                if ($element->getOption('hint_at_bottom') === true) {
                    $markup = sprintf('<fieldset%s>%s%s%s</fieldset>', $attributesString, $legend, $markup, $hint);
                } else {
                    $markup = sprintf('<fieldset%s>%s%s%s</fieldset>', $attributesString, $legend, $hint, $markup);
                }
            }
        }

        if (empty($messages)) {
            return $markup;
        }

        if (!($element instanceof PostcodeSearch)
            && !($element instanceof CompanyNumber)
            && !($element instanceof HoursPerWeek)) {
            return $markup;
        }

        $elementErrors = $this->view->plugin('form_element_errors')->render($element);

        return sprintf('<div class="validation-wrapper">%s%s</div>', $elementErrors, $markup);
    }
}
