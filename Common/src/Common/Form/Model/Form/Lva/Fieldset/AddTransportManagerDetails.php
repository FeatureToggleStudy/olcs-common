<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Add transport manager details fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddTransportManagerDetails
{
    /**
     * @Form\Attributes({"class":"long","id":"", "disabled":"disabled"})
     * @Form\Options({"label":"lva-tm-details-forename"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"class":"long","id":"", "disabled":"disabled"})
     * @Form\Options({"label":"lva-tm-details-familyName"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "lva-tm-details-dob",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("\Zend\Form\Element\DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({"class":"medium", "disabled":"disabled"})
     * @Form\Options({"label":"lva-tm-details-email"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\EmailAddress"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":255}})
     */
    public $email = null;

    /**
     * @Form\Attributes({"value": "markup-lva-tm-add-tm-details-guidance"})
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $guidance = null;
}