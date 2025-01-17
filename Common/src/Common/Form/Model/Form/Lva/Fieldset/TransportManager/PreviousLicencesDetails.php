<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-previous-licences-details")
 */
class PreviousLicencesDetails
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
     * @Form\Attributes({"class":"long","id":"lic-no"})
     * @Form\Options({"label":"transport-manager.previous-licences.form.lic-no"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":18}})
     */
    public $licNo = null;

    /**
     * @Form\Attributes({"class":"long","id":"holderName"})
     * @Form\Options({"label":"transport-manager.previous-licences.form.holder-name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":90}})
     */
    public $holderName = null;
}
