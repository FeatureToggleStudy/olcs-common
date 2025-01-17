<?php

/**
 * Transaction Amount formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

// need to alias as RefData exists in Formatter namespace
use Common\RefData as Ref;

/**
 * Transaction Amount formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionAmount extends Money
{
    /**
     * Format a transaction amount
     *
     * @param array $data
     * @param array $column
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @return string
     */
    public static function format($data, $column = array())
    {
        $amount = parent::format($data, $column);

        if (isset($data['status']['id'])
            && $data['status']['id'] !== Ref::TRANSACTION_STATUS_COMPLETE
        ) {
            return sprintf('<span class="void">%s</span>', $amount);
        }

        return $amount;
    }
}
