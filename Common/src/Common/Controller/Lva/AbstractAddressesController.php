<?php

/**
 * Shared logic between Addresses controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva;

/**
 * Shared logic between Addresses controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractAddressesController extends AbstractController
{
    /**
     * Type map
     *
     * @var array
     */
    protected $typeMap = array(
        'phone_t_tel' => 'phone_business',
        'phone_t_home' => 'phone_home',
        'phone_t_mobile' => 'phone_mobile',
        'phone_t_fax' => 'phone_fax'
    );

    protected $section = 'addresses';

    /**
     * Addresses section
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $addressData = $this->formatDataForForm(
            $this->getServiceLocator()->get('Entity\Licence')->getAddressesData(
                $this->getLicenceId()
            )
        );

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $addressData;
        }

        $typeOfLicence = $this->getTypeOfLicenceData();

        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm($typeOfLicence['licenceType'])
            ->setData($data);

        $hasProcessed = $this->getServiceLocator()->get('Helper\Form')->processAddressLookupForm($form, $request);

        if (!$hasProcessed && $request->isPost()) {
            if ($data['consultant']['add-transport-consultant'] === 'N') {
                $this->getServiceLocator()->get('Helper\Form')
                    ->disableValidation(
                        $form->getInputFilter()->get('consultant')
                    );
            }

            if ($form->isValid()) {
                $response = $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('Lva\\' . ucfirst($this->lva) . 'Addresses')
                    ->process(
                        [
                            'licenceId' => $this->getLicenceId(),
                            'data' => $data,
                            'originalData' => $addressData
                        ]
                    );

                if (!$response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage($response->getMessage());
                    return $this->renderForm($form);
                }

                $this->postChange($response->getData());

                return $this->completeSection('addresses');
            }
        }

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/addresses']);

        return $this->renderForm($form);
    }

    /**
     * Format data for form
     *
     * @param array $data
     * @return array
     */
    private function formatDataForForm($data)
    {
        $returnData = array(
            'contact' => array(
                'phone-validator' => true
            )
        );

        if (!empty($data['correspondenceCd'])) {
            $returnData = $this->formatAddressDataForForm($returnData, $data, 'correspondence');
            $returnData['contact']['email'] = $data['correspondenceCd']['emailAddress'];

            foreach ($data['correspondenceCd']['phoneContacts'] as $phoneContact) {

                $phoneType = $this->mapFormTypeFromDbType($phoneContact['phoneContactType']['id']);

                $returnData['contact'][$phoneType] = $phoneContact['phoneNumber'];
                $returnData['contact'][$phoneType . '_id'] = $phoneContact['id'];
                $returnData['contact'][$phoneType . '_version'] = $phoneContact['version'];
            }
        }

        if (!empty($data['establishmentCd'])) {
            $returnData = $this->formatAddressDataForForm($returnData, $data, 'establishment');
        }

        if (!empty($data['transportConsultantCd'])) {
            $returnData = $this->formatConsultantDataForForm($data);
        }

        return $returnData;
    }

    protected function formatAddressDataForForm($returnData, $data, $type)
    {
        $address = $data[$type . 'Cd'];

        $returnData[$type] = array(
            'id' => $address['id'],
            'version' => $address['version'],
            'fao' => $address['fao']
        );

        $returnData[$type . '_address'] = $address['address'];
        $returnData[$type . '_address']['countryCode'] = $address['address']['countryCode']['id'];

        return $returnData;
    }

    /**
     * Format the consultant data for display within the form.
     *
     * @param array $data
     *
     * @return array
     */
    protected function formatConsultantDataForForm(array $data)
    {
        $data = $data['transportConsultantCd'];

        $returnData['add-transport-consultant'] = 'Y';
        $returnData['writtenPermissionToEngage'] = $data['writtenPermissionToEngage'];
        $returnData['transportConsultantName'] = $data['fao'];
        $returnData['address'] = $data['address'];

        foreach ($data['phoneContacts'] as $phoneContact) {
            $phoneType = $this->mapFormTypeFromDbType($phoneContact['phoneContactType']['id']);

            $returnData['contact'][$phoneType] = $phoneContact['phoneNumber'];
            $returnData['contact'][$phoneType . '_id'] = $phoneContact['id'];
            $returnData['contact'][$phoneType . '_version'] = $phoneContact['version'];
        }

        return array('consultant' => $returnData);
    }

    /**
     * Map form type from db type
     *
     * @param string $type
     */
    private function mapFormTypeFromDbType($type)
    {
        return (isset($this->typeMap[$type]) ? $this->typeMap[$type] : '');
    }

    protected function renderForm($form)
    {
        return $this->render('addresses', $form);
    }

    protected function postChange(array $data)
    {
        unset($data);
        $this->postSave('addresses');
    }
}
