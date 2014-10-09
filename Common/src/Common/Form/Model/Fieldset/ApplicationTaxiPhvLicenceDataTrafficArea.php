<?php

namespace Common\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("dataTrafficArea")
 */
class ApplicationTaxiPhvLicenceDataTrafficArea
{

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "application_taxi-phv_licence.trafficArea.label.new",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "hint": "application_taxi-phv_licence.trafficArea.hint.new"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $trafficArea = null;

    /**
     * @Form\Attributes({"value":"application_taxi-phv_licence.trafficArea.label.exists"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $trafficAreaInfoLabelExists = null;

    /**
     * @Form\Attributes({"value":"<b>%NAME%</b>"})
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $trafficAreaInfoNameExists = null;

    /**
     * @Form\Attributes({"value":"application_taxi-phv_licence.trafficArea.labelasahint.exists"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $trafficAreaInfoHintExists = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $hiddenId = null;


}

