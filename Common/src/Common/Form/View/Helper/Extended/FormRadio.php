<?php

/**
 * Here we extend the View helper to allow us to add attributes that aren't in ZF2's whitelist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\View\Helper\Extended;

use Common\View\Helper\UniqidGenerator;
use Zend\Form\LabelAwareInterface;
use Zend\Form\Element\MultiCheckbox as MultiCheckboxElement;

/**
 * Here we extend the View helper to allow us to add attributes that aren't in ZF2's whitelist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormRadio extends \Zend\Form\View\Helper\FormRadio
{
    use PrepareAttributesTrait;

    /** @var UniqidGenerator */
    protected $idGenerator;

    /**
     * FormRadio constructor.
     * @param UniqidGenerator $idGenerator
     */
    public function __construct(UniqidGenerator $idGenerator = null)
    {
        $this->idGenerator = $idGenerator ?? new UniqidGenerator();
    }

    protected function renderOptions(
        MultiCheckboxElement $element,
        array $options,
        array $selectedOptions,
        array $attributes
    ) {
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $labelHelper = $this->getLabelHelper();
        $labelClose = $labelHelper->closeTag();
        $labelPosition = $this->getLabelPosition();
        $globalLabelAttributes = [];
        $closingBracket = $this->getInlineClosingBracket();
        $radiosWrapperAttributes = $this->makeRadiosWrapperAttributes($attributes);

        if ($element instanceof LabelAwareInterface) {
            $globalLabelAttributes = $element->getLabelAttributes();
        }

        if (empty($globalLabelAttributes)) {
            $globalLabelAttributes = $this->labelAttributes;
        }

        $combinedMarkup = [];
        $count = 0;

        foreach ($options as $key => $optionSpec) {
            $count++;
            if ($count > 1 && array_key_exists('id', $attributes)) {
                unset($attributes['id']);
            }

            $value = '';
            $label = '';
            $hint = '';
            $hintAttributes = '';
            $inputAttributes = $attributes;
            $labelAttributes = $globalLabelAttributes;
            $itemWrapperAttributes = '';
            $selected = (isset($inputAttributes['selected']) && $inputAttributes['type'] != 'radio' && $inputAttributes['selected']);
            $disabled = (isset($inputAttributes['disabled']) && $inputAttributes['disabled']);

            if (is_scalar($optionSpec)) {
                $optionSpec = array(
                    'label' => $optionSpec,
                    'value' => $key
                );
            }

            $optionSpec = $this->addGovUkRadioStyles($optionSpec);

            if (isset($optionSpec['value'])) {
                $value = $optionSpec['value'];
            }
            if (isset($optionSpec['label'])) {
                $label = $optionSpec['label'];
            }
            if (isset($optionSpec['hint_attributes'])) {
                $hintAttributes = $this->createAttributesString($optionSpec['hint_attributes']);
            }
            if (isset($optionSpec['hint'])) {
                $hint = $this->wrapWithTag($optionSpec['hint'], $hintAttributes);
            }
            if (isset($optionSpec['selected'])) {
                $selected = $optionSpec['selected'];
            }
            if (isset($optionSpec['disabled'])) {
                $disabled = $optionSpec['disabled'];
            }
            if (isset($optionSpec['label_attributes'])) {
                $labelAttributes = (isset($labelAttributes))
                    ? array_merge($labelAttributes, $optionSpec['label_attributes'])
                    : $optionSpec['label_attributes'];
            }
            if (isset($optionSpec['attributes'])) {
                $inputAttributes = array_merge($inputAttributes, $optionSpec['attributes']);
            }
            if (isset($optionSpec['item_wrapper_attributes'])) {
                $itemWrapperAttributes = $this->createAttributesString($optionSpec['item_wrapper_attributes']);
            }

            if (in_array($value, $selectedOptions)) {
                $selected = true;
            }

            $inputAttributes['value'] = $value;
            $inputAttributes['checked'] = $selected;
            $inputAttributes['disabled'] = $disabled;

            $inputAttributes = $this->maybeAddInputId($inputAttributes);
            $labelAttributes['for'] = $inputAttributes['id'];

            $input = sprintf(
                '<input %s%s',
                $this->createAttributesString($inputAttributes),
                $closingBracket
            );

            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label,
                    $this->getTranslatorTextDomain()
                );
            }

            if (!$element instanceof LabelAwareInterface || !$element->getLabelOption('disable_html_escape')) {
                $label = $escapeHtmlHelper($label);
            }

            $labelOpen = $labelHelper->openTag($labelAttributes);

            switch ($labelPosition) {
                case self::LABEL_PREPEND:
                    $template = $labelOpen . '%s' . $labelClose . '%s%s';
                    $markup = sprintf($template, $label, $input, $hint);
                    break;
                case self::LABEL_APPEND:
                default:
                    $template = '%s' . $labelOpen . '%s' . $labelClose . '%s';
                    $markup = sprintf($template, $input, $label, $hint);
                    break;
            }

            $combinedMarkup[] = $this->wrapWithTag($markup, $itemWrapperAttributes);
        }

        $outputMarkup = implode($this->getSeparator(), $combinedMarkup);

        if ($outputMarkup !== '') {
            $outputMarkup = $this->wrapWithTag(
                $outputMarkup,
                $this->createAttributesString($radiosWrapperAttributes)
            );
        }

        return $outputMarkup;
    }

    protected function wrapWithTag(string $content, string $attributes = '', string $tag = 'div'): string
    {
        return '<' . $tag . ' ' . $attributes . '>' . $content . '</' . $tag . '>';
    }

    protected function addGovUkRadioStyles(array $valueOptions): array
    {

        $gdsAttributes = [
            'item_wrapper_attributes' => [
                'class' => 'govuk-radios__item',
            ],
            'attributes' => [
                'class' => 'govuk-radios__input',
            ],
            'label_attributes' => [
                'class' => 'govuk-label govuk-radios__label',
            ],
            'hint_attributes' => [
                'class' => 'govuk-hint govuk-radios__hint',
            ],
        ];

        foreach ($gdsAttributes as $key => $attributes) {
            if (isset($valueOptions[$key]) && isset($valueOptions[$key]['class'])) {
                $valueOptions[$key]['class'] .= ' ' . $attributes['class'];
            } elseif (isset($valueOptions[$key])) {
                $valueOptions[$key] = array_merge($valueOptions[$key], $attributes);
            } else {
                $valueOptions[$key] = $attributes;
            }
        }

        return $valueOptions;
    }

    protected function maybeAddInputId(array $inputAttributes): array
    {
        if (!isset($inputAttributes['id'])) {
            $inputAttributes['id'] = $this->idGenerator->generateId();
        }
        return $inputAttributes;
    }

    protected function makeRadiosWrapperAttributes(array $attributes): array
    {
        $radiosWrapperAttributes = [
            'class' => 'govuk-radios'
        ];

        if (isset($attributes['radios_wrapper_attributes'])) {
            foreach ($attributes['radios_wrapper_attributes'] as $key => $value) {
                if (isset($radiosWrapperAttributes[$key])) {
                    $radiosWrapperAttributes[$key] = $radiosWrapperAttributes[$key] . ' ' . $value;
                } else {
                    $radiosWrapperAttributes[$key] = $value;
                }
            }
        }

        return $radiosWrapperAttributes;
    }
}
