<?php

/**
 * Phone Contact business service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Phone Contact business service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PhoneContact implements
    BusinessServiceInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Phone types
     *
     * @var array
     */
    protected $phoneTypes = array(
        'business' => 'phone_t_tel',
        'home' => 'phone_t_home',
        'mobile' => 'phone_t_mobile',
        'fax' => 'phone_t_fax'
    );

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $data = $params['data'];
        $correspondenceId = $params['correspondenceId'];

        $this->savePhoneNumbers($data, $correspondenceId);

        $response = new Response();
        $response->setType(Response::TYPE_SUCCESS);

        return $response;
    }

    /**
     * Save phone numbers
     *
     * @param array $data
     * @param int $correspondenceId
     */
    protected function savePhoneNumbers($data, $correspondenceId)
    {
        foreach ($this->phoneTypes as $phoneType => $phoneRefName) {

            $phone = array(
                'id' => $data['contact']['phone_' . $phoneType . '_id'],
                'version' => $data['contact']['phone_' . $phoneType . '_version'],
            );

            if (!empty($data['contact']['phone_' . $phoneType])) {

                $phone['phoneNumber'] = $data['contact']['phone_' . $phoneType];
                $phone['phoneContactType'] = $phoneRefName;
                $phone['contactDetails'] = $correspondenceId;

                $this->getServiceLocator()->get('Entity\PhoneContact')->save($phone);

            } elseif ((int)$phone['id'] > 0) {
                $this->getServiceLocator()->get('Entity\PhoneContact')->delete($phone['id']);
            }
        }
    }

    /**
     * Map form type from db type
     *
     * @param string $type
     */
    private function mapPhoneTypeFromDbType($type)
    {
        $typeMap = array_flip($this->phoneTypes);

        return (isset($typeMap[$type]) ? $typeMap[$type] : '');
    }

    /**
     * Get fields
     *
     * @param array $phoneContacts
     * @return array
     */
    public function mapPhoneFieldsFromDb($phoneContacts)
    {
        $fields = [];

        foreach ($phoneContacts as $phoneContact) {
            // map form type
            $phoneType = $this->mapPhoneTypeFromDbType($phoneContact['phoneContactType']['id']);

            if (!empty($phoneType)) {
                $fields['phone_'.$phoneType] = $phoneContact['phoneNumber'];
                $fields['phone_'.$phoneType . '_id'] = $phoneContact['id'];
                $fields['phone_'.$phoneType . '_version'] = $phoneContact['version'];
            }
        }

        return $fields;
    }
}
