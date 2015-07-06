<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Type("Zend\Form\Fieldset")
 */
class TypeOfLicence
{
    /**
     * @Form\Name("operator-location")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-operator-location",
     *      "fieldset-attributes": {
     *          "id": "operator-location",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-location",
     *      "label": "application_type-of-licence_operator-location.data.niFlag",
     *      "value_options":{
     *          "N":"Great Britain",
     *          "Y":"Northern Ireland"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $operatorLocation = null;

    /**
     * @Form\Name("operator-type")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-operator-type",
     *      "fieldset-attributes": {
     *          "id": "operator-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "operator-type",
     *      "label": "application_type-of-licence_operator-type.data.goodsOrPsv",
     *      "value_options":{
     *          "lcat_gv":"Goods vehciles",
     *          "lcat_psv":"Public service vehicles"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $operatorType = null;

    /**
     * @Form\Name("licence-type")
     * @Form\Attributes({"id": ""})
     * @Form\Options({
     *      "short-label": "short-label-tol-licence-type",
     *      "fieldset-attributes": {
     *          "id": "licence-type",
     *          "class": "checkbox"
     *      },
     *      "fieldset-data-group": "licence-type",
     *      "label": "application_type-of-licence_licence-type.data.licenceType",
     *      "value_options":{
     *          "ltyp_r": "Restricted",
     *          "ltyp_sn": "Standard National",
     *          "ltyp_si": "Standard International",
     *          "ltyp_sr": "Special Restricted"
     *      }
     * })
     * @Form\Type("Radio")
     */
    public $licenceType = null;
}
