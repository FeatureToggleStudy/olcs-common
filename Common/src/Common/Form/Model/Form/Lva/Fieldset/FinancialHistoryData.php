<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class FinancialHistoryData
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
     * @Form\Attributes({"value":"markup-application_previous-history_financial-history-finance-hint"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $hasAnyPerson = null;

    /**
     * @Form\Annotations({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-bankrupt",
     *     "label": "application_previous-history_financial-history.finance.bankrupt",
     *     "legend-attributes": {"class": "form-element__question"},
     *     "error-message": "financialHistoryData_bankrupt-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"bankrupt"}
     * })
     * @Form\Type("radio")
     */
    public $bankrupt = null;

    /**
     * @Form\Annotations({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-liquidation",
     *     "label": "application_previous-history_financial-history.finance.liquidation",
     *     "legend-attributes": {"class": "form-element__question"},
     *     "error-message": "financialHistoryData_liquidation-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"liquidation"}
     * })
     * @Form\Type("radio")
     */
    public $liquidation = null;

    /**
     * @Form\Annotations({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-receivership",
     *     "label": "application_previous-history_financial-history.finance.receivership",
     *     "legend-attributes": {"class": "form-element__question"},
     *     "error-message": "financialHistoryData_receivership-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id": "receiversip"}
     * })
     * @Form\Type("radio")
     */
    public $receivership = null;

    /**
     * @Form\Annotations({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-administration",
     *     "label": "application_previous-history_financial-history.finance.administration",
     *     "legend-attributes": {"class": "form-element__question"},
     *     "error-message": "financialHistoryData_administration-error",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "fieldset-attributes": {"id":"administration"}
     * })
     * @Form\Type("radio")
     */
    public $administration = null;

    /**
     * @Form\Annotations({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-disqualified",
     *     "fieldset-attributes": {"id":"disqualified"},
     *     "legend-attributes": {"class": "form-element__question"},
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label": "application_previous-history_financial-history.finance.disqualified",
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"},
     *     "error-message": "financialHistoryData_disqualified-error"
     * })
     * @Form\Type("radio")
     */
    public $disqualified = null;

    /**
     * @Form\Attributes({
     *     "value":"markup-application_previous-history_financial-history-insolvencyDetails-hint"
     * })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $additionalInfoLabel = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "required": false,
     *     "id": "",
     *     "class": "long js-financial-history",
     *     "placeholder": "application_previous-history_financial-history.insolvencyDetails.placeholder",
     *     "x-js-hint-chars-count": "application_previous-history_financial-history.insolvencyDetails.count-hint",
     * })
     * @Form\Options({
     *     "short-label": "short-label-financial-history-additional-information",
     *     "label": "short-label-financial-history-additional-information",
     *     "error-message": "financialHistoryData_insolvencyDetails-error",
     *     "label_attributes": {
     *         "class": "visually-hidden",
     *         "id": "additional-information"
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "Common\Form\Elements\Validators\FHAdditionalInfo"})
     */
    public $insolvencyDetails = null;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Attributes({"id":"file"})
     */
    public $file = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-insolvency",
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "application_previous-history_financial-history.insolvencyConfirmation.title",
     *     "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced", 
     *         "id":"insolvency"
     *     },
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $insolvencyConfirmation = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $niFlag = null;
}
