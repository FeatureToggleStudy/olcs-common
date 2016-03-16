<?php

/**
 * Abstract Type Of Licence Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\AbstractLvaFormService;
use Zend\Form\Form;
use Common\RefData;

/**
 * Abstract Type Of Licence Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTypeOfLicence extends AbstractLvaFormService
{
    const ALLOWED_OPERATOR_LOCATION_NI = 'NI';
    const ALLOWED_OPERATOR_LOCATION_GB = 'GB';

    public function getForm($params = [])
    {
        $form = $this->getFormHelper()->createForm('Lva\TypeOfLicence');

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(Form $form, $params = [])
    {
        // no op
    }

    protected function allElementsLocked(Form $form)
    {
        // no op
    }

    protected function lockElements(Form $form, $params = [])
    {
        $typeOfLicenceFieldset = $form->get('type-of-licence');

        // Change labels
        $typeOfLicenceFieldset->get('operator-location')->setLabel('operator-location');
        $typeOfLicenceFieldset->get('operator-type')->setLabel('operator-type');
        $typeOfLicenceFieldset->get('licence-type')->setLabel('licence-type');

        // Add padlocks
        $this->getFormHelper()->lockElement(
            $typeOfLicenceFieldset->get('operator-location'),
            'operator-location-lock-message'
        );
        $this->getFormHelper()->lockElement(
            $typeOfLicenceFieldset->get('operator-type'),
            'operator-type-lock-message'
        );

        // Disable elements
        $this->getFormHelper()->disableElement($form, 'type-of-licence->operator-location');
        $this->getFormHelper()->disableElement($form, 'type-of-licence->operator-type');

        // Optional disable and lock type of licence
        if (!$params['canUpdateLicenceType']) {
            // Disable and lock type of licence
            $this->getFormHelper()->disableElement($form, 'type-of-licence->licence-type');
            $this->getFormHelper()->lockElement(
                $typeOfLicenceFieldset->get('licence-type'),
                'licence-type-lock-message'
            );

            $this->allElementsLocked($form);
        }

        if (!$params['canBecomeSpecialRestricted']) {
            $this->getFormHelper()->removeOption(
                $typeOfLicenceFieldset->get('licence-type'),
                RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            );
        }
    }

    /**
     * Set and lock operator location
     *
     * @param Form $form
     * @param string $location
     */
    public function setAndLockOperatorLocation($form, $location)
    {
        $typeOfLicenceFieldset = $form->get('type-of-licence');
        if ($location === self::ALLOWED_OPERATOR_LOCATION_NI) {
            $typeOfLicenceFieldset->get('operator-location')->setValue('Y');
            $message = 'alternative-operator-location-lock-message-ni';
        } elseif ($location === self::ALLOWED_OPERATOR_LOCATION_GB) {
            $typeOfLicenceFieldset->get('operator-location')->setValue('N');
            $message = 'alternative-operator-location-lock-message-gb';
        }
        $formHelper = $this->getFormHelper();
        $formHelper->disableElement($form, 'type-of-licence->operator-location');
        $formHelper->lockElement($typeOfLicenceFieldset->get('operator-location'), $message);
    }
}
