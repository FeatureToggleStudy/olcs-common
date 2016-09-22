<?php

namespace Common\Service\Data;

use Common\Service\Data\Interfaces\ListData;
use Common\Service\Data\AbstractDataService;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountryList;

/**
 * Class Country
 *
 * @package Common\Service\Data
 */
class Country extends AbstractDataService implements ListData
{
    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['countryDesc'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param string $category  Category
     * @param bool   $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($category, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        if ('isMemberState' == $category) {
            $data = $this->removeNonMemberStates($data);
        }

        return $this->formatData($data);
    }

    /**
     * Remove non-member states
     *
     * @param array $data Data
     *
     * @return array
     */
    public function removeNonMemberStates($data)
    {
        $members = [];

        foreach ($data as $state) {

            if (trim($state['isMemberState']) == 'Y') {

                $members[] = $state;
            }
        }

        return $members;
    }

    /**
     * Fetch list data
     *
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('Country'))) {
            $params = [
                'sort' => 'countryDesc',
                'order' => 'ASC'
            ];
            $dtoData = CountryList::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('Country', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('Country', $response->getResult()['results']);
            }
        }

        return $this->getData('Country');
    }
}
