<?php

/**
 * Received Amount Validator
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\Form\Elements\Validators;

/**
 * Received Amount Validator
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReceivedAmount extends \Zend\Validator\GreaterThan
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_GREATER_INCLUSIVE => "Part payments are permitted but the amount entered is insufficient to
            allocate any payment to one or more of the selected fees.",
        self::NOT_GREATER => "The input is not greater than '%min%'",
    );

    public function isValid($value, $context = null)
    {
        if (isset($context['minAmountForValidator'])) {
            $this->setMin($context['minAmountForValidator']);
        }

        $this->setInclusive(true);

        return parent::isValid($value);
    }
}
