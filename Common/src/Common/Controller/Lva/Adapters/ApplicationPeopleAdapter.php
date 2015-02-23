<?php

/**
 * Common (aka Internal) Application People Adapter
 *
 * @NOTE: currently identical and not abstracted at all
 * in common between all three LVAs
 *
 * This will change in the next story to be developed
 * OLCS-6543
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Zend\Form\Form;

/**
 * Common (aka Internal) Application People Adapter
 *
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends VariationPeopleAdapter
{
    public function alterFormForOrganisation(Form $form, $table, $orgId, $orgType)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return parent::alterFormForOrganisation($form, $table, $orgId, $orgType);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return parent::alterAddOrEditFormForOrganisation($form, $orgId, $orgType);
    }

    public function canModify($orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return true;
        }

        return parent::canModify($orgId);
    }
}
