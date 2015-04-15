<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Zend\Form\Annotation as Form;

/**
 * NOTE: This is shared between the internal tm form, and the lva tm form
 *
 * @Form\Attributes({"class":"table__form"})
 * @Form\Name("details")
 */
class Responsibilities
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
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium",  "multiple" : true})
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.tm-application-oc"
     * })
     * @Form\Type("Select")
     */
    public $operatingCentres = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.tm-type",
     *     "category": "tm_type",
     *     "fieldset-attributes": {
     *         "class": "checkbox inline"
     *     }
     * })
     * @Form\Type("DynamicRadio")
     * @Form\Validator({
     *      "name":"Zend\Validator\NotEmpty"
     * })
     */
    public $tmType = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.is-owner",
     *     "value_options":{
     *         "Y":"Yes",
     *         "N":"No"
     *     },
     *     "fieldset-attributes": {
     *         "class": "checkbox inline"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $isOwner = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.hours-per-week",
     *     "subtitle": "transport-manager.responsibilities.hours-per-week-subtitle"
     * })
     * @Form\Type("Common\Form\Elements\Types\HoursPerWeek")
     */
    public $hoursOfWeek = null;

    /**
     * @Form\Name("otherLicences")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $otherLicences = null;

    /**
     * @Form\Type("TextArea")
     * @Form\Attributes({
     *      "class":"long"
     * })
     * @Form\Options({
     *     "label": "transport-manager.responsibilities.additional-information",
     *     "help-block": "Please provide additional information relating to any prior insolvency proceedings.
     You may also upload evidence such as a legal documents.",
     *     "label_attributes": {
     *         "class": "long"
     *     },
     *     "column-size": "",
     * })
     *
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({
     *      "name":"Zend\Validator\StringLength",
     *      "options":{
     *          "max":4000
     *      }
     * })
     */
    public $additionalInformation;

    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\Type("\Common\Form\Elements\Types\MultipleFileUpload")
     */
    public $file = null;
}