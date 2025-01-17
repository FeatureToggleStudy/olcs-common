<?php

namespace Common\Form\View\Helper;

use Zend\Form\FormInterface as ZendFormInterface;
use Zend\Form\FieldsetInterface;
use Zend\Form\Element\Hidden;

/**
 * Form element view helper
 */
class Form extends \Zend\Form\View\Helper\Form
{
    /**
     * Render a form from the provided $form
     *  We override the parent here as we want to
     *   a. Add logging
     *   b. Ensure fieldsets come before elements
     *
     * @param ZendFormInterface $form            Form
     * @param bool              $includeFormTags Is include form tags
     *
     * @return string
     */
    public function render(ZendFormInterface $form, $includeFormTags = true)
    {
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }

        $fieldsets = $elements = array();
        $hiddenSubmitElement = '';

        /** @var \Zend\View\Renderer\PhpRenderer $view */
        $view = $this->getView();

        if ($form->getAttribute('action') === null) {
            // set the action, point being to remove any anchor (eg #validation-summary) from the URL
            $form->setAttribute('action', $_SERVER['REQUEST_URI']);
        }

        $view->formCollection()->setReadOnly($form->getOption('readonly'));

        /** @var callable $rowHelper */
        $rowHelper = (
            $form->getOption('readonly') ?
            $view->plugin('readonlyformrow') :
            $view->plugin('formrow')
        );

        /** @var \Zend\Form\ElementInterface|FieldsetInterface $element */
        foreach ($form as $element) {
            if ($element instanceof FieldsetInterface) {
                $canKeepEmptyFieldset = $element->hasAttribute('keepEmptyFieldset')
                    ? (bool) $element->getAttribute('keepEmptyFieldset')
                    : false;

                // do not display empty fieldsets as per OLCS-12318
                if (!$element->count() && !$canKeepEmptyFieldset) {
                    continue;
                }

                if ($this->isAllChildsHidden($element) === true) {
                    $element->setAttribute('class', 'hidden');
                }

                // In relation to: OLCS-16809 and OLCS-16630.  Read comments.
                // Not the best method, but the table element is treated as a fieldset.
                // So InputFilter does not set messages on isValid().
                // Now the validation is done, we manipulate where the messages are shown.
                if ($element->has('rows')) {
                    if ($element->get('rows')->getMessages()) {
                        $errors = $element->get('rows')->getMessages();
                        $element->get('rows')->setMessages([]);
                        $element->get('table')->setMessages($errors);
                    }
                }

                $fieldsets[] = $view->addTags(
                    $view->formCollection($element)
                );
            } elseif ($element->getName() === 'form-actions[continue]') {
                $hiddenSubmitElement = $rowHelper($element);
            } else {
                $elements[] = $rowHelper($element);
            }
        }

        return sprintf(
            '%s%s%s%s%s',
            $includeFormTags ? $this->openTag($form) : '',
            $hiddenSubmitElement,
            implode("\n", $fieldsets),
            implode("\n", $elements),
            $includeFormTags ? $this->closeTag() : ''
        );
    }

    /**
     * Check is all children (and their children) is hidden
     *
     * @param FieldsetInterface $fieldset Checked fieldset elemen
     *
     * @return bool
     */
    private function isAllChildsHidden(\Zend\Form\FieldsetInterface $fieldset)
    {
        //  iterate by elements
        /** @var \Zend\Form\Element $element */
        foreach ($fieldset->getElements() as $element) {
            if (!$element instanceof Hidden) {
                return false;
            }
        }

        //  iterate by child fieldsets
        /** @var \Zend\Form\FieldsetInterface $child */
        foreach ($fieldset->getFieldsets() as $child) {
            if ($this->isAllChildsHidden($child) === false) {
                return false;
            }
        }

        return true;
    }
}
