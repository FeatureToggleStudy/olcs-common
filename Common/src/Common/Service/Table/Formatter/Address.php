<?php

/**
 * Address formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Address formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Address implements FormatterInterface
{
    protected static $allFields = [
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'addressLine4',
        'town',
        'postcode',
        'countryCode'
    ];

    /**
     * Format an address
     *
     * @param array $data
     * @param array $column
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @return string
     */
    public static function format($data, $column = array(), $sm = null)
    {
        if (isset($column['name'])) {
            if (strpos($column['name'], '->')) {
                $data = $sm->get('Helper\Data')->fetchNestedData($data, $column['name']);
            } elseif (isset($data[$column['name']])) {
                $data = $data[$column['name']];
            }
        }

        if (isset($column['addressFields'])) {

            if ($column['addressFields'] == 'FULL') {
                $fields = self::$allFields;
            } else {
                $fields = $column['addressFields'];
            }
        } else {
            $fields = array(
                'addressLine1',
                'town'
            );
        }

        $parts = array();

        if (isset($data['countryCode']['id'])) {
            $data['countryCode'] = $data['countryCode']['id'];
        } else {
            $data['countryCode'] = null;
        }

        foreach ($fields as $item) {

            if (isset($data[$item]) && !empty($data[$item])) {

                $parts[] = $data[$item];
            }
        }

        return static::formatAddress($parts);
    }

    /**
     * How to format the resulting address fields. Comma separated.
     *
     * @param $parts
     * @return string
     */
    protected static function formatAddress($parts)
    {
        return implode(', ', $parts);
    }
}
