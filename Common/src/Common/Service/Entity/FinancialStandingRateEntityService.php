<?php

/**
 * Financial Standing Rate Entity Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\Service\Entity;

/**
 * Financial Standing Rate Entity Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 *
 * @see Common\Service\Document\BookmarkFstandingAdditionalVeh
 * @see Common\Service\Document\BookmarkFstandingFirstVeh
 * @deprecated this can be removed once Bookmarks have migrated
 */
class FinancialStandingRateEntityService extends AbstractEntityService
{
    /**
     * Define entity for default behaviour
     *
     * @var string
     */
    protected $entity = 'FinancialStandingRate';

    protected $ratesBundle = [
        'children' => [ 'goodsOrPsv', 'licenceType' ]
    ];

    public function getRecordById($id)
    {
        return $this->get($id, $this->ratesBundle);
    }

    public function getFullList()
    {
        $query = [
            'sort' => 'effectiveFrom',
            'order' => 'ASC'
        ];

        return $this->getAll($query, $this->ratesBundle);
    }

    /**
     * Get all current rates
     */
    public function getRatesInEffect($date = null)
    {
        if (is_null($date)) {
            $date = $this->getServiceLocator()->get('Helper\Date')->getDate();
        }
        $query = [
            'effectiveFrom' => '<='.$date,
            'deletedDate' => 'NULL',
            // in case old rates have not yet been deleted, we'll sort to return
            // the most up-to-date data first
            'sort' => 'effectiveFrom',
            'order' => 'DESC',
        ];

        $data = $this->get($query, $this->ratesBundle);

        return $data['Results'];
    }
}
