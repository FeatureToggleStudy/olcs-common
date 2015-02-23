<?php

namespace Common\Service\Document\Bookmark\Formatter;

/**
 * Address formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Address implements FormatterInterface
{
    protected static $_separator = "\n";

    public static function format(array $data)
    {
        $keys = [
            'addressLine1',
            'addressLine2',
            'addressLine3',
            'addressLine4',
            'town',
            'postcode'
        ];

        $address = [];

        foreach ($keys as $key) {
            if (!empty($data[$key])) {
                $address[] = $data[$key];
            }
        }

        return implode(static::getSeparator(), $address);
    }

    /**
     * @param $separator
     * @return $this
     */
    public static function setSeparator($separator)
    {
        static::$_separator = $separator;
    }

    /**
     * @return string
     */
    public static function getSeparator()
    {
        return static::$_separator;
    }
}
