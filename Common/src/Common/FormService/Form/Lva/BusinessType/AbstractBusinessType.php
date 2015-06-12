<?php

/**
 * Abstract Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\AbstractFormService;
use Zend\Form\Form;

/**
 * Abstract Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessType extends AbstractFormService
{
    public function getForm($inForceLicences)
    {
        $form = $this->getFormHelper()->createForm('Lva\BusinessType');

        $params = [
            'inForceLicences' => $inForceLicences
        ];

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(Form $form, $params)
    {
        // Noop
    }

    protected function lockForm(Form $form)
    {
        $element = $form->get('data')->get('type');

        $this->getFormHelper()->lockElement($element, 'business-type.locked');

        $this->getFormHelper()->disableElement($form, 'data->type');
    }
}