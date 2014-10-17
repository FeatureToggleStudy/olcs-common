<?php

/**
 * Shared logic between Business Details controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Traits\Lva;

use Common\Service\Entity\OrganisationEntityService;
use Common\Service\Entity\AddressEntityService;
use Common\Service\Helper\FormHelperService;

/**
 * Shared logic between Business Details controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait BusinessDetailsTrait
{
    use GenericLvaTrait,
        CrudTableTrait;

    protected $section = 'business_details';

    /**
     * Business details section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $orgId = $this->getCurrentOrganisationId();
        // we *always* want to get org data because we rely on it in
        // alterForm which is called irrespective of whether we're doing
        // a GET or a POST
        $orgData = $this->getServiceLocator()->get('Entity\Organisation')->getBusinessDetailsData($orgId);

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->formatDataForForm($orgData);
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\BusinessDetails');

        $this->alterForm($form, $orgData)
            ->setData($data);

        if ($form->has('table')) {
            $this->populateTable($form, $orgId);
        }

        if ($request->isPost()) {
            /**
             * we'll re-use this in a few places, so cache the lookup
             * just for the sake of legibility
             */
            $tradingNames = isset($data['data']['tradingNames']) ? $data['data']['tradingNames'] : array();

            if (isset($data['data']['companyNumber']['submit_lookup_company'])) {
                $this->processCompanyLookup($data, $form);
            } elseif (isset($tradingNames['submit_add_trading_name'])) {
                $this->processTradingNames($tradingNames, $form);
            } elseif ($form->isValid()) {
                $this->processSave($tradingNames, $orgId, $data);

                if (isset($data['table']['action'])) {
                    return $this->handleCrudAction($data['table']);
                }

                return $this->completeSection('business_details');
            }
        }

        return $this->render('business_details', $form);
    }

    /**
     * User has pressed 'Find company' on registered company number
     */
    private function processCompanyLookup($data, $form)
    {
        if (strlen(trim($data['data']['companyNumber']['company_number'])) === 8) {

            $result = $this->getServiceLocator()
                ->get('Data\CompaniesHouse')
                ->search('numberSearch', $data['data']['companyNumber']['company_number']);

            if ($result['Count'] == 1) {

                $form->get('data')->get('name')->setValue($result['Results'][0]['CompanyName']);
                return;
            }

            $message = 'company_number.search_no_results.error';
        } else {
            $message = 'company_number.length.validation.error';
        }

        $form->get('data')->get('companyNumber')->setMessages(
            array(
                'company_number' => array($message)
            )
        );
    }

    /**
     * User has pressed 'Add another' on trading names
     */
    private function processTradingNames($tradingNames, $form)
    {
        $form->setValidationGroup(array('data' => ['tradingNames']));

        if ($form->isValid()) {

            // remove existing entries from collection and check for empty entries
            $names = [];
            foreach ($tradingNames['trading_name'] as $val) {
                if (!empty(trim($val))) {
                    $names[] = $val;
                }
            }
            $names[] = '';

            $form->get('data')->get('tradingNames')->get('trading_name')->populateValues($names);
        }
    }

    /**
     * Normal submission; save the form data
     */
    private function processSave($tradingNames, $orgId, $data)
    {
        if (isset($tradingNames['trading_name']) && !empty($tradingNames['trading_name'])) {
            $tradingNames = $this->formatTradingNamesDataForSave($orgId, $data);
            $this->getServiceLocator()->get('Entity\TradingNames')->save($tradingNames);
        }

        if (isset($data['data']['registeredAddress'])) {
            $this->saveRegisteredAddress($orgId, $data['data']['registeredAddress']);
        }

        $saveData = $this->formatDataForSave($data);
        $saveData['id'] = $orgId;
        $this->getServiceLocator()->get('Entity\Organisation')->save($saveData);
    }

    /**
     * Save the organisations registered address
     *
     * @param int $orgId
     * @param array $address
     */
    private function saveRegisteredAddress($orgId, $address)
    {
        $saved = $this->getServiceLocator()->get('Entity\Address')->save($address);

        // If we didn't have an address id, then we need to link it to the organisation
        if (!isset($address['id']) || empty($address['id'])) {
            $contactDetailsData = array(
                'organisation' => $orgId,
                'address' => $saved['id'],
                'contactType' => AddressEntityService::CONTACT_TYPE_REGISTERED_ADDRESS
            );

            $this->getServiceLocator()->get('Entity\ContactDetails')->save($contactDetailsData);
        }
    }

    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    private function addOrEdit($mode)
    {
        $orgId = $this->getCurrentOrganisationId();
        $request = $this->getRequest();

        $data = array();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $data = $this->formatCrudDataForForm(
                $this->getServiceLocator()->get('Entity\CompanySubsidiary')->getById($this->params('child_id'))
            );
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\BusinessDetailsSubsidiaryCompany')
            ->setData($data);

        // @todo this could do with drying up
        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatCrudDataForSave($data);
            $data['organisation'] = $orgId;

            $this->getServiceLocator()->get('Entity\CompanySubsidiary')->save($data);

            return $this->handlePostSave();
        }

        return $this->render($mode . '_subsidiary_company', $form);
    }

    private function formatTradingNamesDataForSave($organisationId, $data)
    {
        $tradingNames = [];

        foreach ($data['data']['tradingNames']['trading_name'] as $tradingName) {
            if (trim($tradingName) !== '') {
                $tradingNames[] = [
                    'name' => $tradingName
                ];
            }
        }

        $data['tradingNames'] = $tradingNames;

        return array(
            'organisation' => $organisationId,
            'licence' => $this->getLicenceId(),
            'tradingNames' => $tradingNames
        );
    }

    /**
     * Format data for save
     *
     * @param array $data
     * @return array
     */
    private function formatDataForSave($data)
    {
        return array(
            'version' => $data['version'],
            'companyOrLlpNo' => isset($data['data']['companyNumber']['company_number'])
            ? $data['data']['companyNumber']['company_number']
            : null,
            'name' => isset($data['data']['name']) ? $data['data']['name'] : null
        );
    }

    /**
     * Format data for form
     *
     * @param array $data
     * @return array
     */
    private function formatDataForForm($data)
    {
        $tradingNames = array();
        foreach ($data['tradingNames'] as $tradingName) {
            $tradingNames[] = $tradingName['name'];
        }

        $registeredAddress = array();

        foreach ($data['contactDetails'] as $contactDetail) {
            if ($contactDetail['contactType']['id'] == AddressEntityService::CONTACT_TYPE_REGISTERED_ADDRESS) {
                $registeredAddress = $contactDetail['address'];
                continue;
            }
        }

        return array(
            'version' => $data['version'],
            'data' => array(
                'companyNumber' => array(
                    'company_number' => $data['companyOrLlpNo']
                ),
                'tradingNames' => array(
                    'trading_name' => $tradingNames
                ),
                'name' => $data['name'],
                'type' => $data['type']['id'],
                'registeredAddress' => $registeredAddress
            )
        );
    }

    /**
     * Format subsidiary data for save
     *
     * @param array $data
     * return array
     */
    private function formatCrudDataForSave($data)
    {
        return $data['data'];
    }

    /**
     * Format subsidiary data for form
     *
     * @param array $data
     * return array
     */
    private function formatCrudDataForForm($data)
    {
        return array('data' => $data);
    }

    /**
     * Alter form based on available data
     *
     * @param \Zend\Form\Form $form
     * @param array $data
     * @return \Zend\Form\Form
     */
    private function alterForm($form, $data)
    {
        $orgType = $data['type']['id'];

        $fieldset = $form->get('data');

        $element = $fieldset->get('editBusinessType');
        $element->setOptions(
            array_merge(
                $element->getOptions(),
                array('route' => 'lva-' . $this->lva . '/business_type')
            )
        );

        // we have to manually set the business type, otherwise if this
        // was a POST it won't come through (it's a disabled element)
        // and will default to the first value (limited company)
        $fieldset->get('type')->setValue($orgType);

        switch ($orgType) {
            case OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY:
            case OrganisationEntityService::ORG_TYPE_LLP:
                // no-op; the full form is fine
                break;

            case OrganisationEntityService::ORG_TYPE_SOLE_TRADER:
                $this->alterFormForNonRegisteredCompany($form);
                $this->getServiceLocator()->get('Helper\Form')
                    ->remove($form, 'data->name');
                break;

            case OrganisationEntityService::ORG_TYPE_PARTNERSHIP:
                $this->alterFormForNonRegisteredCompany($form);
                $this->appendToLabel($fieldset->get('name'), '.partnership');
                break;

            case OrganisationEntityService::ORG_TYPE_OTHER:
                $this->getServiceLocator()->get('Helper\Form')
                    ->remove($form, 'data->tradingNames');
                $this->alterFormForNonRegisteredCompany($form);
                $this->appendToLabel($fieldset->get('name'), '.other');
                break;
        }

        return $form;
    }

    /**
     * Append to an element label
     *
     * @param \Zend\Form\Element $element
     * @param string $append
     */
    private function appendToLabel($element, $append)
    {
        $this->getServiceLocator()->get('Helper\Form')
            ->alterElementLabel($element, $append, FormHelperService::ALTER_LABEL_APPEND);
    }

    /**
     * Make generic form alterations for non limited (or LLP) companies
     *
     * @param \Zend\Form\Form $form
     */
    private function alterFormForNonRegisteredCompany($form)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'table')
            ->remove($form, 'data->companyNumber')
            ->remove($form, 'data->registeredAddress');
    }

    private function populateTable($form, $orgId)
    {
        $tableData = $this->getServiceLocator()->get('Entity\CompanySubsidiary')
            ->getAllForOrganisation($orgId);

        $table = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable('lva-subsidiaries', $tableData);

        $form->get('table')  // fieldset
            ->get('table')   // element
            ->setTable($table);
    }

    /**
     * Mechanism to *actually* delete a subsidiary, invoked by the
     * underlying delete action
     */
    protected function delete()
    {
        $this->getServiceLocator()->get('Entity\CompanySubsidiary')
            ->delete($this->params('child_id'));
    }
}
