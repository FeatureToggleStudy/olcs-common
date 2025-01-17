<?php

namespace Common\Util;

use Zend\View\Helper\EscapeHtml;

/**
 * Contains escape functions
 *
 * @author Dmitrij Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Escape
{
    /** @var  callable */
    private static $fncHtml;

    public static function html($html)
    {
        if (self::$fncHtml === null) {
            self::$fncHtml = new EscapeHtml();
        }

        $fnc = self::$fncHtml;
        return $fnc($html);
    }
}
