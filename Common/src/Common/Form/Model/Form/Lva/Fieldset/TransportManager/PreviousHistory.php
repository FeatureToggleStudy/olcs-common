<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"table__form","id":"previousHistory"})
 * @Form\Name("previousHistory")
 */
class PreviousHistory
{
    /**
     * @Form\Options({
     *     "label": "transport-manager.convictions-and-penalties.form.radio.label",
     *     "hint" : "transport-manager.convictions-and-penalties.form.radio.hint",
     *     "hint-class" : "",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Validator({
     *     "name":"Common\Form\Elements\Validators\YesNoTableRequiredValidator",
     *     "options": {
     *          "table": "convictions",
     *          "message":"transport-manager-details.form.convictions.required"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $hasConvictions = null;

    /**
     * @Form\Name("convictions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"previousConvictions",
     *      "class": "help__text help__text--removePadding"
     * })
     */
    public $convictions = null;

    /**
     * @Form\Options({
     *     "label": "transport-manager.previous-licences.form.radio.label",
     *     "hint" : "transport-manager.previous-licences.form.radio.hint",
     *     "hint-class" : "",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Validator({
     *     "name":"Common\Form\Elements\Validators\YesNoTableRequiredValidator",
     *     "options": {
     *          "table": "previousLicences",
     *          "message":"transport-manager-details.form.previouslicences.required"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $hasPreviousLicences = null;

    /**
     * @Form\Name("previousLicences")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *      "id":"previousLicences",
     *      "class": "help__text help__text--removePadding"
     * })
     */
    public $previousLicences = null;
}
