<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class PreviousConvictionData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formTitle",
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $title = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formFirstName",
     *     "label_attributes": {
     *         "aria-label": "Enter first names"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formLastName",
     *     "label_attributes": {
     *         "aria-label": "Enter family names"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $familyName = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob","required":false})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formDateOfConviction",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $convictionDate = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formOffence",
     *     "label_attributes": {
     *         "aria-label": "Enter your offence"
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $categoryText = null;

    /**
     * @Form\Attributes({"id":"","class":"long"})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetails",
     *     "label_attributes": {
     *         "class": "col-sm-2",
     *         "aria-label": "Give details of the offence"
     *     },
     *     "column-size": "sm-6",
     *     "help-block": "selfserve-app-subSection-previous-history-criminal-conviction-helpBlock",
     *     "hint": "selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetaisHelpBlock"
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $notes = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formNameOfCourt",
     *     "label_attributes": {
     *         "aria-label": "Enter the name of the court"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $courtFpn = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *     "label": "selfserve-app-subSection-previous-history-criminal-conviction-formPenalty",
     *     "label_attributes": {
     *         "aria-label": "Enter the penalty"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $penalty = null;
}
