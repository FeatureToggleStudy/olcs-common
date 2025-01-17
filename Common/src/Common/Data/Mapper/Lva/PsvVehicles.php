<?php

/**
 * Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Zend\Form\Form;

/**
 * Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehicles implements MapperInterface
{
    public static function mapFromResult(array $data)
    {
        return [
            'data' => [
                'version' => $data['version'],
                // @NOTE: licences don't have this flag, but we haven't defined their behaviour
                // on PSV pages yet. As such, this just prevents a PHP error
                'hasEnteredReg' => isset($data['hasEnteredReg']) ? $data['hasEnteredReg'] : 'Y'
            ],
            'shareInfo' => [
                'shareInfo' => isset($data['organisation']['confirmShareVehicleInfo'])
                    ? $data['organisation']['confirmShareVehicleInfo']
                    : null
            ]
        ];
    }

    public static function mapFormErrors(Form $form, array $errors, FlashMessengerHelperService $fm)
    {
        $formMessages = [];

        if (isset($errors['hasEnteredReg'])) {

            foreach ($errors['hasEnteredReg'] as $key => $message) {
                $formMessages['data']['hasEnteredReg'][] = $message;
            }

            unset($errors['hasEnteredReg']);
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }
}
