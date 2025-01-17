<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Common\Service\Data\AbstractDataService;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewContextList;

/**
 * BusRegSearchView List data service.
 * Populates filter drop down lists on bus reg registrations page.
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class BusRegSearchViewListDataService extends AbstractDataService implements ListData
{
    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        return $this->formatData($data, $context);
    }

    /**
     * Format data
     *
     * @param array  $data    Data
     * @param string $context Context
     *
     * @return array
     */
    public function formatData(array $data, $context)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum[$this->getKeyField($context)]] = $datum[$this->getValueField($context)];
        }

        return $optionData;
    }

    /**
     * Fetch list data
     *
     * @param string $context Context
     *
     * @return array
     * @throw UnexpectedResponseException
     */
    public function fetchListData($context)
    {
        $cacheId = 'BusRegSearchView' . ucfirst($context);

        if ($this->getData($cacheId) === null) {
            $dtoData = BusRegSearchViewContextList::create(
                [
                    'context' => $context,
                    'sort' => $this->getValueField($context),
                    'order' => 'ASC'
                ]
            );

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData($cacheId, false);
            $result = $response->getResult();

            if (isset($result['results'])) {
                $this->setData($cacheId, $result['results']);
            }
        }

        return $this->getData($cacheId);
    }

    /**
     * Get the Value field to use in the drop downs based on context
     *
     * @param string $context Context
     *
     * @return string
     * @throws DataServiceException
     */
    private function getValueField($context)
    {
        switch($context) {
            case 'licence':
                return 'licNo';
            case 'organisation':
                return 'organisationName';
            case 'busRegStatus':
                return 'busRegStatusDesc';
            default:
                throw new DataServiceException('Invalid context value used in data service');
        }
    }

    /**
     * Get the Key field to use in the drop downs based on context
     *
     * @param string $context Context
     *
     * @return string
     * @throws DataServiceException
     */
    private function getKeyField($context)
    {
        switch($context) {
            case 'licence':
                return 'licId';
            case 'organisation':
                return 'organisationId';
            case 'busRegStatus':
                return 'busRegStatus';
            default:
                throw new DataServiceException('Invalid context key used in data service');
        }
    }
}
