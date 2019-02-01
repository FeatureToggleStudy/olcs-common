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
class InternalLicencePermitReference implements FormatterInterface
{
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
        $urlHelper = $serviceLocator->get('Helper\Url');
        $url = $urlHelper->fromRoute('licence/permits/application', [
            'licence' => $row['licenceId'],
            'action' => 'edit',
            'permitid' => $row['id']
        ]);
        return '<a href="'.$url.'">'.Escape::html($row['applicationRef']).'</a>';
    }
}
