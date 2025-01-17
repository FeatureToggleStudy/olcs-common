<?php

/**
 * LicenceNumberLink.php
 */
namespace Common\Service\Table\Formatter;

use Common\Service\Entity\LicenceEntityService;

/**
 * Class LicenceNumberLink
 *
 * Takes a licence array and creates and outputs a link for that licence.
 *
 * @package Common\Service\Table\Formatter
 */
class LicenceNumberLink implements FormatterInterface
{
    /**
     * Return a the licence URL in a link format for a table.
     *
     * @param array $data The row data.
     * @param array $column The column
     * @param null $sm The service manager
     *
     * @return string
     */
    public static function format($data, $column = array(), $sm = null)
    {
        unset($column);

        $permittedLicenceStatuses = array(
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED
        );

        if (in_array($data['licence']['status'], $permittedLicenceStatuses)) {
            $urlHelper = $sm->get('Helper\Url');
            $url = $urlHelper->fromRoute('lva-licence', array('licence' => $data['licence']['id']));

            return '<a href="' . $url . '">' . $data['licence']['licNo'] . '</a>';
        }

        return $data['licence']['licNo'];
    }
}
