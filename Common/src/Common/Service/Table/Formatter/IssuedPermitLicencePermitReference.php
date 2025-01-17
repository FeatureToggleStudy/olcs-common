<?php

/**
 * Status formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Status formatter
 *
 *
 */
class IssuedPermitLicencePermitReference implements FormatterInterface
{
    /**
     * status
     *
     * @param array $row Row data
     * @param array $column Column data
     * @param \Zend\ServiceManager\ServiceManager $serviceLocator Service locator
     *
     * @return string
     * @inheritdoc
     */
    public static function format($row, $column = null, $serviceLocator = null)
    {
        $urlHelper = $serviceLocator->get('Helper\Url');
        $url = $urlHelper->fromRoute(
            'licence/irhp-permits',
            [
                'licence' => $row['licenceId'],
                'permitid' => $row['id'],
                'permitTypeId' => $row['typeId']
            ]
        );
        return '<a href="' . $url . '">' . Escape::html($row['applicationRef']) . '</a>';
    }
}
