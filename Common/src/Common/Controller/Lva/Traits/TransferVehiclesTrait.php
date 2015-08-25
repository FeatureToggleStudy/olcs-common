<?php

/**
 * Transfer Vehicles Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Licence\OtherActiveLicences;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;

/**
 * Transfer Vehicles Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait TransferVehiclesTrait
{
    /**
     * Transfer vehicles
     */
    protected function transferVehicles()
    {
        $response = $this->handleQuery(OtherActiveLicences::create(['id' => $this->getLicenceId()]));

        $options = [];

        foreach ($response->getResult()['otherActiveLicences'] as $licence) {
            $options[$licence['id']] = $licence['licNo'];
        }

        $form = $this->getVehicleTransferForm($options);

        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setData((array) $request->getPost());

            if ($form->isValid()) {

                $formData = $form->getData();

                $ids = explode(',', $this->params()->fromRoute('child_id'));

                $dtoData = [
                    'id' => $this->getLicenceId(),
                    'target' => $formData['data']['licence'],
                    'licenceVehicles' => $ids
                ];

                $response = $this->handleCommand(TransferVehicles::create($dtoData));

                $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

                if ($response->isOk()) {

                    $fm->addSuccessMessage('licence.vehicles_transfer.form.vehicles_transfered');

                    return $this->redirect()->toRouteAjax(
                        null,
                        [$this->getIdentifierIndex() => $this->getIdentifier()]
                    );
                }

                if ($response->isClientError()) {

                    $messages = $response->getResult()['messages'];
                    $th = $this->getServiceLocator()->get('Helper\Translation');
                    $licNo = $options[$formData['data']['licence']];

                    $knownError = false;

                    if (isset($messages['LIC_TRAN_1'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace('licence.vehicles_transfer.form.message_exceed', [$licNo])
                        );

                        $knownError = true;
                    }

                    if (isset($messages['LIC_TRAN_2'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace(
                                'licence.vehicles_transfer.form.message_already_on_licence_singular',
                                [
                                    implode(', ', json_decode($messages['LIC_TRAN_2'], true)),
                                    $licNo
                                ]
                            )
                        );

                        $knownError = true;
                    }

                    if (isset($messages['LIC_TRAN_3'])) {
                        $fm->addErrorMessage(
                            $th->translateReplace(
                                'licence.vehicles_transfer.form.message_already_on_licence',
                                [
                                    implode(', ', json_decode($messages['LIC_TRAN_3'], true)),
                                    $licNo
                                ]
                            )
                        );

                        $knownError = true;
                    }

                    if ($knownError == false) {
                        $fm->addCurrentErrorMessage('unknown-error');
                    } else {
                        return $this->redirect()->toRouteAjax(
                            null,
                            [$this->getIdentifierIndex() => $this->getIdentifier()]
                        );
                    }
                }

                if ($response->isServerError()) {
                    $fm->addCurrentErrorMessage('unknown-error');
                }
            }
        }

        return $this->render('transfer_vehicles', $form);
    }

    /**
     * Get vehicles transfer form
     *
     * @return \Zend\Form\Form
     */
    protected function getVehicleTransferForm($options)
    {
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('Lva\VehiclesTransfer', $this->getRequest());

        $form->get('data')->get('licence')->setValueOptions($options);

        return $form;
    }
}