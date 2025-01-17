<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * Subsidiary company
 */
class SubsidiaryCompany
{
    use VersionTrait;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"class":"long","id":"name"})
     * @Form\Options({
     *     "label":"application_your-business_business-details-formName",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "subsidiary-company-name-error"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     */
    public $name = null;

    /**
     * @Form\Attributes({
     *     "class":"long",
     *     "id":"companyNo",
     *     "pattern":"\d*"
     * })
     * @Form\Options({
     *     "label":"application_your-business_business-details-formCompanyNo",
     *     "label_attributes": {"class": "form-element__question"},
     *     "error-message": "subsidiary-company-number-error"
     * })
     * @Form\Type("Common\Form\Elements\InputFilters\CompanyNumber")
     */
    public $companyNo = null;
}
