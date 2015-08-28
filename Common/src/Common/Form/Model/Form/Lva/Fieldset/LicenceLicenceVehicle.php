<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licence-vehicle")
 */
class LicenceLicenceVehicle
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
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.receivedDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "default_date": "now"
     * })
     * @Form\Required(false)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $receivedDate = null;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.specifiedDate",
     *     "create_empty_option": false,
     *     "render_delimiters": false,
     *     "default_date": "now"
     * })
     * @Form\Required(false)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $specifiedDate = null;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.removalDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "month_attributes": {"disabled":"disabled"},
     *     "year_attributes": {"disabled":"disabled"},
     *     "day_attributes": {"disabled":"disabled"}
     * })
     * @Form\Required(false)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $removalDate = null;

    /**
     * @Form\Attributes({})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.seedDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $warningLetterSeedDate = null;

    /**
     * @Form\Attributes({"disabled":"disabled"})
     * @Form\Options({
     *     "label": "application_vehicle-safety_vehicle-sub-action.licence-vehicle.discNo"
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $discNo = null;
}
