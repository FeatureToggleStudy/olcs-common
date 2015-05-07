<?php

/**
 * LicenceNumberLink.php
 */
namespace Common\Service\Table\Formatter;

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

        $urlHelper = $sm->get('Helper\Url');
        $url = $urlHelper->fromRoute('lva-licence', array('licence' => $data['licence']['id']));

        return '<a href="' . $url . '">' . $data['licence']['licNo'] . '</a>';
    }
}
