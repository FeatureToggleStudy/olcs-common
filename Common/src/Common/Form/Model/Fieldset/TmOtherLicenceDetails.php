<?php

namespace Common\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-other-licence-details")
 */
class TmOtherLicenceDetails
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
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $redirectAction = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $redirectId = null;

    /**
     * @Form\Attributes({"class":"long","id":"licNo"})
     * @Form\Options({"label":"transport-manager.other-licence.form.lic-no"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Type("Text")
     */
    public $licNo = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "transport-manager.other-licence.form.role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "other_lic_role"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $role = null;

    /**
     * @Form\Attributes({"class":"long","id":"operatingCentres"})
     * @Form\Options({"label":"transport-manager.other-licence.form.operating-centres"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Type("Text")
     */
    public $operatingCentres = null;

    /**
     * @Form\Attributes({"class":"long","id":"totalAuthVehicles"})
     * @Form\Options({"label":"transport-manager.other-licence.form.total-auth-vehicles"})
     * @Form\Required(false)
     * @Form\Validator({"name":"Digits"})
     * @Form\Type("Text")
     */
    public $totalAuthVehicles = null;

    /**
     * @Form\Attributes({"class":"long","id":"hoursPerWeek"})
     * @Form\Options({"label":"transport-manager.other-licence.form.hours-per-week"})
     * @Form\Validator({"name": "Zend\Validator\NotEmpty"})
     * @Form\Validator({"name":"Digits"})
     * @Form\Type("Text")
     */
    public $hoursPerWeek = null;
}
