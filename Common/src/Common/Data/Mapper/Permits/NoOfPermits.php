<?php

namespace Common\Data\Mapper\Permits;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use RuntimeException;

/**
 * No of permits mapper
 */
class NoOfPermits
{
    /**
     * @param array $data
     * @param $form
     * @param TranslationHelperService $translator
     * @param string $irhpApplicationDataKey
     * @param string $maxPermitsByStockDataKey
     * @param string $feePerPermitDataKey
     *
     * @return array
     */
    public static function mapForFormOptions(
        array $data,
        $form,
        TranslationHelperService $translator,
        $irhpApplicationDataKey,
        $maxPermitsByStockDataKey,
        $feePerPermitDataKey
    ) {
        $permitTypeId = $data[$irhpApplicationDataKey]['irhpPermitType']['id'];

        switch ($permitTypeId) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                return BilateralNoOfPermits::mapForFormOptions(
                    $data,
                    $form,
                    $translator,
                    $irhpApplicationDataKey,
                    $maxPermitsByStockDataKey,
                    $feePerPermitDataKey
                );
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                return MultilateralNoOfPermits::mapForFormOptions(
                    $data,
                    $form,
                    $translator,
                    $irhpApplicationDataKey,
                    $maxPermitsByStockDataKey,
                    $feePerPermitDataKey
                );
            default:
                throw new RuntimeException('Unsupported permit type ' . $permitTypeId);
        }
    }
}
