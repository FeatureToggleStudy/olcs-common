<?php

/**
 * Status formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;
use Common\Util\Escape;

/**
 * Status formatter
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class LicencePermitReference implements FormatterInterface
{
    private static $routes = [
        RefData::ECMT_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application-overview',
            RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION => 'ecmt-under-consideration',
            RefData::PERMIT_APP_STATUS_AWAITING_FEE => 'ecmt-awaiting-fee',
            RefData::PERMIT_APP_STATUS_FEE_PAID => null,
            RefData::PERMIT_APP_STATUS_ISSUING => null,
            RefData::PERMIT_APP_STATUS_VALID => 'ecmt-valid-permits',
        ],
        RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::ECMT_REMOVAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::IRHP_BILATERAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => [
            RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED => 'application',
            RefData::PERMIT_APP_STATUS_VALID => 'valid',
        ],
    ];

    /**
     * status
     *
     * @param array                               $row            Row data
     * @param array                               $column         Column data
     * @param \Zend\ServiceManager\ServiceManager $serviceLocator Service locator
     *
     * @return string
     * @inheritdoc
     */
    public static function format($row, $column = null, $serviceLocator = null)
    {
        // find a route for the type and status
        $route = isset(static::$routes[$row['typeId']][$row['statusId']])
            ? static::$routes[$row['typeId']][$row['statusId']] : null;

        if (!isset($route)) {
            return Escape::html($row['applicationRef']);
        }

        // default to application
        $params = [
            'id' => $row['id'],
        ];
        $linkText = $row['applicationRef'];

        if ($route === 'valid') {
            // specific for valid IRHP application
            $params = [
                'licence' => $row['licenceId'],
                'type' => $row['typeId'],
            ];
            $linkText = $row['licNo'];
        }

        return isset($route)
            ? vsprintf(
                '<a class="overview__link" href="%s"><span class="overview__link--underline">%s</span></a>',
                [
                    $serviceLocator->get('Helper\Url')->fromRoute('permits/' . $route, $params),
                    Escape::html($linkText)
                ]
            )
            : Escape::html($row['applicationRef']);
    }
}
