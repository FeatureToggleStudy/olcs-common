<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("evidence")
 */
class FinancialEvidence
{
    /**
     * @Form\Attributes({"value": "markup-required-finance" })
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $requiredFinance = null;

    /**
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "class": "checkbox inline",
     *          "label": "foo"
     *      },
     *     "label": "lva-financial-evidence-upload-now.label",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "help-block": "Please choose",
     *     "label_attributes": {
     *         "class": "inline"
     *     }
     * })
     * @Form\Type("\Zend\Form\Element\Radio")
     */
    public $uploadNow = null;

}
