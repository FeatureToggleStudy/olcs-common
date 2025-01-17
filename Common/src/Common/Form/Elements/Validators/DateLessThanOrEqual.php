<?php
namespace Common\Form\Elements\Validators;

use Zend\Validator\AbstractValidator as AbstractValidator;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Checks a date is not after another date
 */
class DateLessThanOrEqual extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    const NOT_LESS_THAN_OR_EQUAL = 'notLessThanOrEqual';
    const MISSING_TOKEN = 'missingToken';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_LESS_THAN_OR_EQUAL => "This is after a corresponding date, it must be the same date or before",
        self::MISSING_TOKEN => 'No token was provided to match against',
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'token' => 'tokenString'
    );

    /**
     * Original token against which to validate
     * @var string
     */
    protected $tokenString;
    protected $token;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     */
    public function __construct($token = null)
    {
        if ($token instanceof Traversable) {
            $token = ArrayUtils::iteratorToArray($token);
        }

        if (is_array($token) && array_key_exists('token', $token)) {

            $this->setToken($token['token']);
        } elseif (null !== $token) {
            $this->setToken($token);
        }

        parent::__construct(is_array($token) ? $token : null);
    }

    /**
     * Retrieve token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token against which to compare
     *
     * @param  mixed $token
     * @return Identical
     */
    public function setToken($token)
    {
        $this->tokenString = (is_array($token) ? var_export($token, true) : (string) $token);
        $this->token = $token;
        return $this;
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     * @throws Exception\RuntimeException if the token doesn't exist in the context array
     */
    public function isValid($value, array $context = null)
    {
        $thisValue = $value;

        $c = $context[$this->getToken()];
        $compareValue = implode('-', [$c['year'], $c['month'], $c['day']]);

        if (!($thisValue <= $compareValue)) {
            $this->error(self::NOT_LESS_THAN_OR_EQUAL);
            return false;
        }

        return true;
    }
}
