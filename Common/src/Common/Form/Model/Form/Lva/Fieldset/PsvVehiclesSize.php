<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("psv-vehicles-size")
 */
class PsvVehiclesSize
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "class": "checkbox"
     *      },
     *     "label": "application_psv_vehicle_size",
     *     "short-label": "application_psv_vehicle_size",
     *     "disable_inarray_validator": false,
     *     "category": "psv_vehicle_size",
     *     "break_chain_on_failure": true,
     * })
     * @Form\Type("DynamicRadio")
     */
    public $size = null;
}