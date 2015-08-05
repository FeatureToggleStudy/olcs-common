<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("dataTrafficArea")
 * @Form\Attributes({
 *      "class": "traffic-area"
 * })
 */
class TrafficArea
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.dataTrafficArea.label.new",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "hint": "application_operating-centres_authorisation.dataTrafficArea.hint.new"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $trafficArea = null;

    /**
     *
     * @Form\Type("Common\Form\Elements\Types\TrafficAreaSet")
     */
    public $trafficAreaSet = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.enforcementArea.label",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $enforcementArea = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    //public $hiddenId = null;
}
