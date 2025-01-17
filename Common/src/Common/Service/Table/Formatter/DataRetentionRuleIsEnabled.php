<?php

namespace Common\Service\Table\Formatter;

use Zend\ServiceManager\ServiceManager;

/**
 * Data Retention Boolean formatter
 */
class DataRetentionRuleIsEnabled implements FormatterInterface
{
    /**
     * Format
     *
     * @param array $data Data of current row
     *
     * @return string
     */
    public static function format($data)
    {
        return htmlspecialchars($data['isEnabled'] ? 'Yes' : 'No');
    }
}
