<?php

/**
 * Common Lva Abstract Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva;

use Common\Controller\Lva\Interfaces\AdapterAwareInterface;
use Zend\Http\Response;
use Zend\Stdlib\ResponseInterface;

/**
 * Common Lva Abstract Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTypeOfLicenceController extends AbstractController implements AdapterAwareInterface
{
    use Traits\AdapterAwareTrait;

    /**
     * Type of licence section
     */
    public function indexAction()
    {
        $adapter = $this->getAdapter();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->formatDataForForm($this->getTypeOfLicenceData());
        }

        $form = $this->getTypeOfLicenceForm();

        if ($adapter !== null) {
            $form = $adapter->alterForm($form, $this->getIdentifier(), $this->location);
        }

        $form->setData($data);

        if ($request->isPost() && $form->isValid()) {

            $response = $this->processPostAdapter($data);

            if ($response instanceof Response) {
                return $response;
            }

            // If we have an adapter, we need to grab the previous data as we need to check this later
            $previousData = null;
            if ($adapter !== null) {
                $previousData = $this->getTypeOfLicenceData();
            }

            // Update the record
            $data = $this->formatDataForSave($data);
            $data['id'] = $this->getIdentifier();
            $this->getLvaEntityService()->save($data);

            // If we have the adapter, check if we are updating this record for the first time
            if ($adapter !== null && $previousData !== null && !$adapter->isCurrentDataSet($previousData)) {
                $adapter->processFirstSave($data['id']);
            }

            $this->postSave('type_of_licence');

            return $this->completeSection('type_of_licence');
        }

        if ($adapter !== null) {
            $adapter->setMessages($this->getIdentifier(), $this->location);
        }

        $this->getServiceLocator()->get('Script')->loadFile('type-of-licence');

        return $this->render('type_of_licence', $form);
    }

    protected function processPostAdapter($data)
    {
        $adapter = $this->getAdapter();

        if ($adapter === null) {
            return;
        }

        // @NOTE If we haven't got type-of-licence then all of our elements are disabled
        // so we want to skip checking and persisting data, and just move the user on
        if (!isset($data['type-of-licence'])) {
            return $this->completeSection('type_of_licence');
        }

        $currentData = $this->getTypeOfLicenceData();

        if ($adapter->doesChangeRequireConfirmation($data['type-of-licence'], $currentData)) {
            return $this->redirect()->toRoute(null, $adapter->getRouteParams(), $adapter->getQueryParams(), true);
        }

        if ($adapter->processChange($data['type-of-licence'], $currentData)) {
            return $this->completeSection('type_of_licence');
        }
    }

    /**
     * Format data for save
     *
     * @param array $data
     * @return array
     */
    protected function formatDataForSave($data)
    {
        $formattedData = array(
            'version' => $data['version']
        );

        if (isset($data['type-of-licence']['operator-location'])) {
            $formattedData['niFlag'] = $data['type-of-licence']['operator-location'];
        }

        if (isset($data['type-of-licence']['operator-type'])) {
            $formattedData['goodsOrPsv'] = $data['type-of-licence']['operator-type'];
        }

        if (isset($data['type-of-licence']['licence-type'])) {
            $formattedData['licenceType'] = $data['type-of-licence']['licence-type'];
        }

        return $formattedData;
    }

    /**
     * Format data for form
     *
     * @param array $data
     * @return array
     */
    protected function formatDataForForm($data)
    {
        return array(
            'version' => $data['version'],
            'type-of-licence' => array(
                'operator-location' => $data['niFlag'],
                'operator-type' => $data['goodsOrPsv'],
                'licence-type' => $data['licenceType']
            )
        );
    }

    /**
     * Get type of licence form
     *
     * @return \Zend\Form\Form
     */
    protected function getTypeOfLicenceForm()
    {
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('Lva\TypeOfLicence');

        $this->alterFormForLocation($form);
        $this->alterFormForLva($form);

        return $form;
    }

    public function confirmationAction()
    {
        $adapter = $this->getAdapter();

        if ($adapter === null) {
            return $this->notFoundAction();
        }

        // @NOTE will either return a redirect, or a form
        $response = $adapter->confirmationAction();

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return $this->render(
            $adapter->getConfirmationMessage(),
            $response,
            array('sectionText' => $adapter->getExtraConfirmationMessage())
        );
    }
}
