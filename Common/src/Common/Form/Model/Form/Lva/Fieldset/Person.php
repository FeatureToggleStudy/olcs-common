<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class Person
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"id":"title","placeholder":""})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "application_your-business_people-sub-action-formTitle",
     *     "label_attributes": {"class": "form-element__question"},
     *     "category":"person_title",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $title = null;

    /**
     * @Form\Attributes({"class":"long","id":"forename"})
     * @Form\Options({
     *     "label":"application_your-business_people-sub-action-formFirstName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "person_forename-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":0,"max":35}})
     */
    public $forename = null;

    /**
     * @Form\Attributes({"class":"long","id":"familyname"})
     * @Form\Options({
     *    "label":"application_your-business_people-sub-action-formSurname",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "person_familyName-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":0,"max":35}})
     */
    public $familyName = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *    "label":"application_your-business_people-sub-action-formOtherNames",
     *     "label_attributes": {"class": "form-element__question"}
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":0,"max":35}})
     */
    public $otherName = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"application_your-business_people-sub-action-formPosition"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":0,"max":45}})
     */
    public $position = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "dob",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y",
     *     "error-message": "person_birthDate-error",
     *     "fieldset-attributes": {"id":"dob_day"}
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelect", "options":{"null_on_empty":true}})
     * @Form\Validator({"name":"NotEmpty", "options": {"array"}})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name":"\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $birthDate = null;
}
