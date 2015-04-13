<?php

/**
 * Fee Processing Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Processing;

use Common\Service\Data\FeeTypeDataService;
use Common\Service\Data\CategoryDataService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Fee Processing Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class FeeProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function generateDocument($feeTypeName, array $params = [])
    {
        if ($feeTypeName !== FeeTypeDataService::FEE_TYPE_GRANT) {
            return;
        }

        $description = 'Goods Grant Fee Request';

        $storedFile = $this->getServiceLocator()
            ->get('Helper\DocumentGeneration')
            ->generateAndStore('FEE_REQ_GRANT_GV', $description, $params);

        $this->getServiceLocator()
            ->get('PrintScheduler')
            ->enqueueFile($storedFile, $description);

        $this->getServiceLocator()->get('Entity\Document')->createFromFile(
            $storedFile,
            [
                'description'   => $description,
                'filename'      => str_replace(" ", "_", $description) . '.rtf',
                'application'   => $params['application'],
                'licence'       => $params['licence'],
                'fileExtension' => 'doc_rtf',
                'category'      => CategoryDataService::CATEGORY_LICENSING,
                'subCategory'   => CategoryDataService::DOC_SUB_CATEGORY_FEE_REQUEST
            ]
        );
    }
}