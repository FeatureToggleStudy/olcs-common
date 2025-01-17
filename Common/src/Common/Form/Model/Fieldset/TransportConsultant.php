<?php

namespace Common\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("transport-consultant")
 * @Form\Attributes({"class":""})
 */
class TransportConsultant
{
    /**
     * @Form\Name("add-transport-consultant")
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label":"application_your-business_business-type.add-transport-consultant.label",
     *     "legend-attributes": {"class": "form-element__label"},
     *     "value_options":{"Y":"Yes","N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     * })
     * @Form\Attributes({"value":"N"})
     * @Form\Type("\Zend\Form\Element\Radio")
     */
    public $addTransportConsultant = null;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Attributes({"id":"written-permission-to-engage","placeholder":""})
     * @Form\Options({
     *     "label":"application_your-business_business-type.written-perm-engage.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--confirm"},
     *     "checked_value":"Y",
     *     "unchecked_value":"N",
     * })
     */
    public $writtenPermissionToEngage = null;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"class":"long"})
     * @Form\Options({"label":"application_your-business_business-type.consultant-name.label"})
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $transportConsultantName = null;
}
