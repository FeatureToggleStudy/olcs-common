<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\Data\Mapper\Lva\BusinessDetails as Mapper;
use Common\Data\Mapper\Lva\CompanySubsidiary as CompanySubsidiaryMapper;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query\CompanySubsidiary\CompanySubsidiary;
use Dvsa\Olcs\Transfer\Query\Licence\BusinessDetails;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Zend\Form\Form;

/**
 * Shared logic between Business Details Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessDetailsController extends AbstractController
{
    use CrudTableTrait;

    protected $section = 'business_details';
    protected $baseRoute = 'lva-%s/business_details';

    /**
     * Business details section
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $response = $this->handleQuery(BusinessDetails::create(['id' => $this->getLicenceId()]));

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentErrorMessage('unknown-error');
            return $this->notFoundAction();
        }

        $orgData = $response->getResult();

        if ($request->isPost()) {
            $data = $this->getFormPostData($orgData);
        } else {
            $data = Mapper::mapFromResult($orgData);
        }

        // Gets a fully configured/altered form for any version of this section
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()
            ->get('FormServiceManager')
            ->get('lva-' . $this->lva . '-' . $this->section)
            ->getForm($orgData['type']['id'], $orgData['hasInforceLicences'])
            ->setData($data);
        // need to reset Input Filter defaults after the data has been set on the form
        $form->attachInputFilterDefaults($form->getInputFilter(), $form);

        if ($form->has('table')) {
            $this->populateTable($form, $orgData);
        }

        // Added an early return for non-posts to improve the readability of the code
        if (!$request->isPost()) {
            return $this->renderForm($form);
        }

        // If we are performing a company number lookup
        if (isset($data['data']['companyNumber']['submit_lookup_company'])) {
            $this->getServiceLocator()->get('Helper\Form')
                ->processCompanyNumberLookupForm($form, $data, 'data', 'registeredAddress');

            return $this->renderForm($form);
        }

        // We'll re-use this in a few places, so cache the lookup just for the sake of legibility
        $tradingNames = isset($data['data']['tradingNames']) ? $data['data']['tradingNames'] : [];

        // If we are interacting with the trading names collection element
        if (isset($data['data']['submit_add_trading_name'])) {
            $this->processTradingNames($tradingNames, $form);
            return $this->renderForm($form);
        }

        $crudAction = null;

        if (isset($data['table'])) {
            $crudAction = $this->getCrudAction([$data['table']]);
        }

        if ($crudAction !== null) {
            $formHelper = $this->getServiceLocator()->get('Helper\Form');
            $formHelper->disableValidation($form->getInputFilter());
        }

        // If our form is invalid, render the form to display the errors
        if (!$form->isValid()) {
            return $this->renderForm($form);
        }

        if ($this->lva === self::LVA_LIC) {
            $dtoData = [
                'id' => $this->getLicenceId(),
                'version' => $data['version'],
                'name' => $data['data']['name'],
                'tradingNames' => $this->flattenTradingNames($tradingNames),
                'natureOfBusiness' => isset($data['data']['natureOfBusiness'])
                    ? $data['data']['natureOfBusiness'] : null,
                'companyOrLlpNo' => isset($data['data']['companyNumber']['company_number'])
                    ? $data['data']['companyNumber']['company_number'] : null,
                'registeredAddress' => isset($data['registeredAddress']) ? $data['registeredAddress'] : null,
                'partial' => $crudAction !== null,
                'allowEmail' => isset($data['allow-email']['allowEmail'])
                    ? $data['allow-email']['allowEmail'] : null,
            ];

            $response = $this->handleCommand(TransferCmd\Licence\UpdateBusinessDetails::create($dtoData));
        } else {
            $dtoData = [
                'id' => $this->getIdentifier(),
                'licence' => $this->getLicenceId(),
                'version' => $data['version'],
                'name' => $data['data']['name'],
                'tradingNames' => $this->flattenTradingNames($tradingNames),
                'natureOfBusiness' => isset($data['data']['natureOfBusiness'])
                    ? $data['data']['natureOfBusiness'] : null,
                'companyOrLlpNo' => isset($data['data']['companyNumber']['company_number'])
                    ? $data['data']['companyNumber']['company_number'] : null,
                'registeredAddress' => isset($data['registeredAddress']) ? $data['registeredAddress'] : null,
                'partial' => $crudAction !== null
            ];

            $response = $this->handleCommand(TransferCmd\Application\UpdateBusinessDetails::create($dtoData));
        }

        if (!$response->isOk()) {
            $this->mapErrors($form, $response->getResult()['messages']);

            return $this->renderForm($form);
        }

        if ($crudAction !== null) {
            return $this->handleCrudAction($crudAction);
        }

        return $this->completeSection('business_details');
    }

    /**
     * Flatten the array from trading names elements and remove where empty
     *
     * @param array $tradingNames Eg [['name' => 'Trading name 1'], ['name' => ''], ['name' => 'Trading name 2'] ]
     *
     * @return array Eg ['Trading name 1', 'Trading name 2']
     */
    private function flattenTradingNames(array $tradingNames)
    {
        $result = [];
        foreach ($tradingNames as $tradingNameElement) {
            // If name is set (and not empty)
            if (isset($tradingNameElement['name'])) {
                $result[] = $tradingNameElement['name'];
            }
        }
        return $result;
    }

    /**
     * Add Action
     *
     * @return \Common\View\Model\Section
     */
    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    /**
     * Edit action
     *
     * @return \Common\View\Model\Section
     */
    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    /**
     * Method used to render the indexAction form
     *
     * @param \Zend\Form\Form $form Form
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderForm($form)
    {
        $this->getServiceLocator()->get('Script')->loadFiles(['lva-crud']);
        return $this->render('business_details', $form);
    }

    /**
     * Grabs the data from the post, and set's some defaults in-case there are disabled fields
     *
     * @param array $orgData Organisation data
     *
     * @return array
     */
    protected function getFormPostData($orgData)
    {
        $data = (array)$this->getRequest()->getPost();

        if (!isset($data['data']['companyNumber'])
            || !array_key_exists('company_number', $data['data']['companyNumber'])
        ) {
            $data['data']['companyNumber']['company_number'] = $orgData['companyOrLlpNo'];
        }

        if (!array_key_exists('name', $data['data'])) {
            $data['data']['name'] = $orgData['name'];
        }

        return $data;
    }

    /**
     * User has pressed 'Add another' on trading names
     * So we need to duplicate the trading names field to produce another input
     *
     * @param array             $tradingNames Trading names
     * @param \Common\Form\Form $form         Form
     *
     * @return void
     */
    protected function processTradingNames($tradingNames, $form)
    {
        $form->setValidationGroup(array('data' => ['tradingNames']));
        if ($form->isValid()) {
            $tradingNames[]['name'] = '';
            $form->get('data')->get('tradingNames')->populateValues($tradingNames);
        }
    }

    /**
     * Add|edit functionality
     *
     * @param string $mode Mode
     *
     * @return \Common\View\Model\Section
     */
    protected function addOrEdit($mode)
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $id = $this->params('child_id');

        $data = [];

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $entity = ($this->lva === self::LVA_LIC ? 'licence' : 'application');

            $query = CompanySubsidiary::create(['id' => $id, $entity => $this->getIdentifier()]);

            $response = $this->handleQuery($query);

            if ($response->isClientError()) {
                return $this->notFoundAction();
            }

            $data = CompanySubsidiaryMapper::mapFromResult($response->getResult());
        }

        // @todo Move this into a form service
        /** @var \Common\Form\Form $form */
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('Lva\BusinessDetailsSubsidiaryCompany', $request)
            ->setData($data);

        // @todo Add this generic behaviour to a form service
        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($request->isPost() && $form->isValid()) {
            $dtoData = [
                'name' => $data['data']['name'],
                'companyNo' => $data['data']['companyNo'],
            ];

            // Creating
            $isCreate = ($id === null);

            if (!$isCreate) {
                $dtoData['id'] = $id;
                $dtoData['version'] = $data['data']['version'];
            }

            /** @var QueryInterface $dtoClass */
            if ($this->lva === self::LVA_LIC) {
                $dtoData['licence'] = $this->getIdentifier();

                if ($isCreate) {
                    $dtoClass = TransferCmd\Licence\CreateCompanySubsidiary::class;
                } else {
                    $dtoClass = TransferCmd\Licence\UpdateCompanySubsidiary::class;
                }
            } else {
                $dtoData['application'] = $this->getIdentifier();

                if ($isCreate) {
                    $dtoClass = TransferCmd\Application\CreateCompanySubsidiary::class;
                } else {
                    $dtoClass = TransferCmd\Application\UpdateCompanySubsidiary::class;
                }
            }

            $response = $this->handleCommand($dtoClass::create($dtoData));

            if ($response->isOk()) {
                return $this->handlePostSave(null, ['fragment' => 'table']);
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $this->render($mode . '_subsidiary_company', $form);
    }

    /**
     * Populate tables
     *
     * @param \Common\Form\Form $form    Form
     * @param array             $orgData Data
     *
     * @return void
     */
    protected function populateTable($form, $orgData)
    {
        $table = $this->getServiceLocator()->get('Table')
            ->prepareTable('lva-subsidiaries', $orgData['companySubsidiaries']);

        $this->getServiceLocator()->get('Helper\Form')->populateFormTable($form->get('table'), $table);
    }

    /**
     * Mechanism to *actually* delete a subsidiary, invoked by the underlying delete action
     *
     * @return boolean
     */
    protected function delete()
    {
        $data = [
            'ids' => explode(',', $this->params('child_id')),
            $this->getIdentifierIndex() => $this->getIdentifier(),
        ];

        /** @var QueryInterface $dtoClass */
        if ($this->lva === self::LVA_LIC) {
            $dtoClass = TransferCmd\Licence\DeleteCompanySubsidiary::class;
        } else {
            $dtoClass = TransferCmd\Application\DeleteCompanySubsidiary::class;
        }

        $response = $this->handleCommand($dtoClass::create($data));

        return $response->isOk();
    }

    /**
     * Get delete modal title
     *
     * @return string
     */
    protected function getDeleteTitle()
    {
        return 'delete-company-subsidiary';
    }

    /**
     * Map errors
     *
     * @param Form  $form   Form
     * @param array $errors Errors
     *
     * @return void
     */
    protected function mapErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['natureOfBusiness'])) {
            $formMessages['data']['natureOfBusiness'] = $errors['natureOfBusiness'];
            unset($errors['natureOfBusiness']);
        }

        if (!empty($errors)) {
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
