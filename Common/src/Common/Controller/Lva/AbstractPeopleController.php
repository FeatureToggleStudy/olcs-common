<?php

/**
 * Shared logic between People controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva;

use Common\Service\Entity\OrganisationEntityService;
use Common\Controller\Lva\Interfaces\AdapterAwareInterface;

/**
 * Shared logic between People controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractPeopleController extends AbstractController implements AdapterAwareInterface
{
    use Traits\AdapterAwareTrait,
        Traits\CrudTableTrait {
        Traits\CrudTableTrait::deleteAction as originalDeleteAction;
    }

    /**
     * Needed by the Crud Table Trait
     */
    protected $section = 'people';

    /**
     * Index action
     */
    public function indexAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        $adapter->addMessages();

        if ($adapter->isSoleTrader()) {
            return $this->handleSoleTrader();
        }

        /**
         * Could bung this in another method, but since it's everything other
         * than sole trader, it makes no real difference
         */

        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = (array)$request->getPost();

            $crudAction = $this->getCrudAction(array($postData['table']));

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
            $this->updateCompletion();

            return $this->completeSection('people');
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\People');

        $table = $adapter->createTable();

        $form->get('table')
            ->get('table')
            ->setTable($table);

        $this->alterForm($form, $table, $adapter->getOrganisationType());

        $adapter->alterFormForOrganisation($form, $table);

        $this->getServiceLocator()->get('Script')->loadFile('lva-crud-delta');

        return $this->render('people', $form);
    }

    /**
     * Handle indexAction if a sole trader
     *
     * @return type
     */
    private function handleSoleTrader()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array) $request->getPost();
        } else {
            $personData = $adapter->getFirstPersonData();
            if ($personData === false) {
                $data['data'] = [];
            } else {
                $data['data'] = $personData['person'];
                $data['data']['position'] = $personData['position'];
            }
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('Lva\SoleTrader')->setData($data);

        $this->alterFormForLva($form);

        $adapter->alterAddOrEditFormForOrganisation($form);

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatCrudDataForSave($form->getData());

            if ($form->getAttribute('locked') !== true) {
                $this->savePerson($data);
                $this->updateCompletion();
            }

            return $this->completeSection('people');
        }

        return $this->render('person', $form);
    }

    private function updateCompletion()
    {
        if ($this->lva != 'licence') {
            $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Application\UpdateCompletion::create(
                    ['id' => $this->getIdentifier(), 'section' => 'people']
                )
            );
        }
    }

    private function savePerson($formData)
    {
        if (empty($formData['id'])) {
            $this->getAdapter()->create($formData);
        } else {
            $this->getAdapter()->update($formData);
        }
    }

    /**
     * Alter form based on company type
     */
    private function alterForm($form, $table, $organisationTypeId)
    {
        $this->alterFormForLva($form);

        $tableHeader = 'selfserve-app-subSection-your-business-people-tableHeader';
        $guidanceLabel = 'selfserve-app-subSection-your-business-people-guidance';

        // needed in here?
        $translator = $this->getServiceLocator()->get('translator');

        switch ($organisationTypeId) {
            case OrganisationEntityService::ORG_TYPE_REGISTERED_COMPANY:
                $tableHeader .= 'Directors';
                $guidanceLabel .= 'LC';
                break;

            case OrganisationEntityService::ORG_TYPE_LLP:
                $tableHeader .= 'Partners';
                $guidanceLabel .= 'LLP';
                break;

            case OrganisationEntityService::ORG_TYPE_PARTNERSHIP:
                $tableHeader .= 'Partners';
                $guidanceLabel .= 'P';
                break;

            case OrganisationEntityService::ORG_TYPE_OTHER:
                $tableHeader .= 'People';
                $guidanceLabel .= 'O';
                break;

            default:
                break;
        }

        // a separate if saves repeating this three times in the switch...
        if ($organisationTypeId !== OrganisationEntityService::ORG_TYPE_OTHER) {
            $table->removeColumn('position');
        }

        $table->setVariable(
            'title',
            $translator->translate($tableHeader)
        );

        $form->get('guidance')
            ->get('guidance')
            ->setTokens([$guidanceLabel]);
    }

    private function alterCrudForm($form, $mode, $orgData)
    {
        if ($mode !== 'add') {
            $form->get('form-actions')->remove('addAnother');
        }

        if ($orgData['type']['id'] !== OrganisationEntityService::ORG_TYPE_OTHER) {
            // otherwise we're not interested in position at all, bin it off
            $this->getServiceLocator()->get('Helper\Form')
                ->remove($form, 'data->position');
        }
    }

    /**
     * Add person action
     */
    public function addAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        if (!$this->getAdapter()->canModify()) {
            return $this->redirectWithoutPermission();
        }

        return $this->addOrEdit('add');
    }

    /**
     * Edit person action
     */
    public function editAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        return $this->addOrEdit('edit');
    }

    /**
     * Helper method as both add and edit pretty
     * much do the same thing
     *
     * @param string $mode
     */
    private function addOrEdit($mode)
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $request = $this->getRequest();

        $data = array();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } elseif ($mode === 'edit') {
            $personId = (int) $this->params('child_id');
            $personData = $adapter->getPersonData($personId);
            $data['data'] = $personData['person'];
            $data['data']['position'] = $personData['position'];
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('Lva\Person', $request);

        $this->alterCrudForm($form, $mode, $adapter->getOrganisation());

        $adapter->alterAddOrEditFormForOrganisation($form);

        $form->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $data = $this->formatCrudDataForSave($form->getData());

            $this->savePerson($data);

            return $this->handlePostSave(null, false);
        }

        return $this->render($mode . '_people', $form);
    }

    /**
     * Format data from CRUD form
     */
    private function formatCrudDataForSave($data)
    {
        return array_filter(
            $data['data'],
            function ($v) {
                return $v !== null;
            }
        );
    }

    /**
     * Mechanism to *actually* delete a person, invoked by the
     * underlying delete action
     */
    protected function delete()
    {
        $id = $this->params('child_id');
        $ids = explode(',', $id);

        $this->getAdapter()->delete($ids);
    }

    /**
     * Delete person action
     */
    public function deleteAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        if (!$this->getAdapter()->canModify()) {
            return $this->redirectWithoutPermission();
        }

        return $this->originalDeleteAction();
    }

    private function redirectWithoutPermission()
    {
        $this->addErrorMessage('cannot-perform-action');
        return $this->redirect()->toRouteAjax(
            null,
            [$this->getIdentifierIndex() => $this->getIdentifier()]
        );
    }

    public function restoreAction()
    {
        /* @var $adapter Adapters\AbstractPeopleAdapter */
        $adapter = $this->getAdapter();
        $adapter->loadPeopleData($this->lva, $this->getIdentifier());

        $id = $this->params('child_id');
        $ids = explode(',', $id);
        $this->getAdapter()->restore($ids);

        return $this->redirect()->toRouteAjax(
            null,
            [$this->getIdentifierIndex() => $this->getIdentifier()]
        );
    }
}
