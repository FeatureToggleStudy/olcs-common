<?php

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Zend\Form\Form;

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicle implements MapperInterface
{
    public static function mapFromResult(array $data)
    {
        return [
            'data' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'vrm' => $data['vehicle']['vrm'],
                'makeModel' => $data['vehicle']['makeModel'],
            ],
            'licence-vehicle' => [
                'receivedDate' => $data['receivedDate'],
                'specifiedDate' => $data['specifiedDate'],
                'removalDate' => $data['removalDate']
            ]
        ];
    }

    public static function mapFromForm($data)
    {
        $licenceVehicle = [
            'receivedDate' => null,
            'specifiedDate' => null,
            'removalDate' => null
        ];

        if (isset($data['licence-vehicle'])) {
            $licenceVehicle = array_merge($licenceVehicle, $data['licence-vehicle']);
        }

        $mappedData = [
            'version' => $data['data']['version'],
            'vrm' => $data['data']['vrm'],
            'receivedDate' => $licenceVehicle['receivedDate'],
            'specifiedDate' => $licenceVehicle['specifiedDate'],
            'removalDate' => $licenceVehicle['removalDate']
        ];

        if (isset($data['data']['makeModel'])) {
            $mappedData['makeModel'] = $data['data']['makeModel'];
        }
        return $mappedData;
    }

    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm)
    {
        $formMessages = [];

        if (isset($errors['vrm'])) {

            foreach ($errors['vrm'] as $key => $message) {
                $formMessages['data']['vrm'][] = $message;
            }

            unset($errors['vrm']);
        }

        if (isset($errors['removalDate'])) {

            foreach ($errors['removalDate'] as $key => $message) {
                $formMessages['licence-vehicle']['removalDate'][] = $message;
            }

            unset($errors['removalDate']);
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
