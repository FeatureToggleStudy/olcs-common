<?php

namespace Common\Service\Table\Formatter;

use Zend\ServiceManager\ServiceManager;

/**
 * Data Retention Rule admin link formatter
 */
class DataRetentionRuleAdminLink implements FormatterInterface
{
    /**
     * Format
     *
     * @param array          $data   Data of current row
     * @param array          $column Column
     * @param ServiceManager $sm     Service manager
     *
     * @return string
     */
    public static function format($data, $column = array(), $sm = null)
    {
        $urlHelper = $sm->get('Helper\Url');
        $url = $urlHelper->fromRoute(
            'admin-dashboard/admin-data-retention/rule-admin-form',
            ['dataRetentionRuleId' => $data['id']]
        );

        return '<a href="' . $url . '" class="js-modal-ajax">' . ucwords($data['description']) . '</a>';

    }
}
