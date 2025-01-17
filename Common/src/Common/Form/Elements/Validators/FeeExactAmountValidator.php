<?php

/**
 * Fee Exact Amount Validator
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\Form\Elements\Validators;

use Zend\Validator\Identical;

/**
 * Fee Exact Amount Validator
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeExactAmountValidator extends Identical
{
    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SAME      => "Value must match the fee(s) due",
        self::MISSING_TOKEN => 'No token was provided to match against',
    );
}
