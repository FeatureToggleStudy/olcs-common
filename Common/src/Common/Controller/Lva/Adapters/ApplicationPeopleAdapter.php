<?php

/**
 * Common (aka Internal) Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Zend\Form\Form;

/**
 * Common (aka Internal) Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends VariationPeopleAdapter
{
    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return parent::alterFormForOrganisation($form, $table, $orgId);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return parent::alterAddOrEditFormForOrganisation($form, $orgId);
    }

    protected function doesNotRequireDeltas($orgId)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return true;
        }

        $appId = $this->getApplicationAdapter()->getIdentifier();

        $appOrgPeople = $this->getServiceLocator()
            ->get('Entity\ApplicationOrganisationPerson')
            ->getAllByApplication($appId, 1);

        $hasLicences = $this->getServiceLocator()
            ->get('Entity\Organisation')
            ->hasInForceLicences($orgId);

        return $appOrgPeople['Count'] === 0 && !$hasLicences;
    }
}