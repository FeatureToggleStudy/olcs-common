<?php

namespace Common\Service\Data;

use Common\Service\Data\Interfaces\ListData;
use Common\Service\Data\AbstractDataService;
use Dvsa\Olcs\Transfer\Query\Cases\PiVenue\PiVenueList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Zend\ServiceManager\FactoryInterface;

/**
 * Class PiVenue
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PiVenue extends AbstractDataService implements ListData, FactoryInterface
{
    use LicenceServiceTrait;

    /**
     * Format data!
     *
     * @param array $data
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['name'];
        }

        return $optionData;
    }

    /**
     * @param $category
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($category, $useGroups = false)
    {
        $context = $this->getLicenceContext();

        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @internal param $category
     * @return array
     */
    public function fetchListData($params)
    {
        if (is_null($this->getData('PiVenue'))) {
            $params = [
                'trafficArea' => $params['trafficArea'],
            ];
            $dtoData = PiVenueList::create($params);

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }
            $this->setData('PiVenue', false);
            if (isset($response->getResult()['results'])) {
                $this->setData('PiVenue', $response->getResult()['results']);
            }
        }

        return $this->getData('PiVenue');
    }
}
