<?php

namespace Common\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorPluginManagerAwareInterface;
use Zend\Validator\ValidatorPluginManager;

/**
 * Class ValidateIf
 * @package Common\Validator
 */
class ValidateIfMultiple extends ValidateIf
{
    /**
     * @internal This is out of scope from ZF 2.4+.  This is only used for the custom validator.
     *           There is no need to remove this for compatibility.
     *
     * Returns true if and only if $value meets the validation requirements
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param null $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (array_key_exists($this->getContextField(), $context)) {
            if (is_array($context[$this->getContextField()])) {
                foreach ($context[$this->getContextField()] as $optionSelected) {
                    if (!(in_array($optionSelected, $this->getContextValues()) ^ $this->getContextTruth())) {
                        if ($this->allowEmpty() && empty($value)) {
                            return true;
                        }

                        $result = $this->getValidatorChain()->isValid($value, $context);
                        if (!$result) {
                            $this->abstractOptions['messages'] = $this->getValidatorChain()->getMessages();
                        }

                        return $result;
                    }
                }
            }

            return true;

        }

        $this->error(self::NO_CONTEXT);
        return false;
    }
}
