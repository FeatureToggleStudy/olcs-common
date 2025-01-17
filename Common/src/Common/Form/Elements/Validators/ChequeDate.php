<?php

/**
 * Checks a date for a cheque payment is valid
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\Form\Elements\Validators;

use Zend\Validator\AbstractValidator as AbstractValidator;

/**
 * Checks a date for a cheque payment is valid
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ChequeDate extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    const INVALID = 'invalid';

    /**
     * @const string
     */
    const MIN_INTERVAL = '-6 months';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Cheque date cannot be older than 6 months",
    );

    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $date = strtotime('noon '. $value);
        $limit = strtotime('noon' . self::MIN_INTERVAL);

        if ($date < $limit) {
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }
}
