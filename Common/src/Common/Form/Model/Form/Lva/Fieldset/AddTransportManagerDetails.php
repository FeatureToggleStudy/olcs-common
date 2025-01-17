<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Add Transport Manager details fieldset
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
     *     "label": "dob",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $birthDate = null;

    /**
     * @Form\Attributes({"class":"medium", "disabled":"disabled"})
     * @Form\Options({
     *     "label":"lva-tm-details-email",
     *     "label_attributes": {
     *         "aria-label": "Enter their email address"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Dvsa\Olcs\Transfer\Validators\EmailAddress"})
     */
    public $email = null;

    /**
     * @Form\Attributes({"value": "markup-lva-tm-add-tm-details-guidance"})
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $guidance = null;
}
