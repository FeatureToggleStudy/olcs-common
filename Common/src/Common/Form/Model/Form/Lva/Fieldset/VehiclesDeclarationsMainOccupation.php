<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Vehicle Declarations - main occupation (PSV Restricted and > 0 medium vehicles only)
 * @Form\Attributes({
 *      "class": "psv-show-large psv-show-both"
 * })
 */
class VehiclesDeclarationsMainOccupation
{
    /**
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.mainOccupation.confirmation",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $psvMediumVhlConfirmation = null;

    /**
     * @Form\Attributes({"id":"","class":"long"})
     * @Form\Options({
     *     "label": "application_vehicle-safety_undertakings.mainOccupation.notes",
     * })
     * @Form\Type("Textarea")
     * @Form\Validator({"name": "Zend\Validator\StringLength", "options": {"min": 10, "max": 1000}})
     */
    public $psvMediumVhlNotes = null;
}
