<?php

/**
 * Abstract Review Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva\Adapters;

use Common\RefData;
use Common\Controller\Lva\Interfaces\ReviewAdapterInterface;

/**
 * Abstract Review Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReviewAdapter extends AbstractAdapter implements ReviewAdapterInterface
{
    protected $lva = '';

    protected $sectionsToIgnore = [];

    /**
     * Get all sections for a given application id
     *
     * @param int $id
     * @param array $relevantSections
     * @return array
     */
    public function getSectionData($id, array $relevantSections = array())
    {
        //        $sections = $this->filterSections($id, $relevantSections);
//
//        $entity = ucfirst($this->lva);
//
        //        $method = 'getReviewDataFor' . $entity;
        //
        //        // Grab all of the review data in one go
        //        // @todo migrate this
        //        $reviewData = $this->getServiceLocator()->get('Entity\Application')
        //            ->$method($id, $sections);
//
//        $stringHelper = $this->getServiceLocator()->get('Helper\String');
//
//        $sectionConfig = [];
//
//        foreach ($sections as $section) {
//            $serviceName = 'Review\\' . $entity . $stringHelper->underscoreToCamel($section);
//            $config = null;
//
//            // @NOTE this check is in place while we implement each section
//            // eventually we should be able to remove the if
//            if ($this->getServiceLocator()->has($serviceName)) {
//                $service = $this->getServiceLocator()->get($serviceName);
//                $config = $service->getConfigFromData($reviewData);
//            }
//
//            $sectionConfig[] = [
//                'header' => 'review-' . $section,
//                'config' => $config
//            ];
//        }
//
//        return [
//            'reviewTitle' => $this->getTitle($reviewData),
//            'subTitle' => $this->getSubTitle($reviewData),
//            'sections' => $sectionConfig
//        ];
    }

//    protected function getSubTitle($data)
//    {
//        return sprintf('%s %s/%s', $data['licence']['organisation']['name'], $data['licence']['licNo'], $data['id']);
//    }

//    protected function getTitle($data)
//    {
//        return sprintf(
//            '%s-review-title-%s%s',
//            $this->lva,
//            $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE ? 'gv' : 'psv',
//            $this->isNewPsvSpecialRestricted($data) ? '-sr' : ''
//        );
//    }

//    protected function isNewPsvSpecialRestricted($data)
//    {
//        return $this->lva === 'application'
//            && $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV
//            && $data['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED;
//    }

    /**
     * Filter unwanted sections
     *
     * @param int $id
     * @param array $sections
     * @return array
     */
    protected function filterSections($id, $sections)
    {
        return array_values(array_diff($sections, $this->sectionsToIgnore));
    }
}
