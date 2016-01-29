<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data")
 */
class ConvictionsPenaltiesData
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *      "fieldset-attributes": {
     *          "class": "question checkbox inline"
     *      },
     *     "label":
     * "selfserve-app-subSection-previous-history-criminal-conviction-hasConv",
     *     "value_options": {"Y": "Yes", "N": "No"},
     * })
     * @Form\Type("\Zend\Form\Element\Radio")
     * @Form\Validator({"name":"Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     "options": {
     *         "name": "noConviction"
     *      }
     * })
     */
    public $question = null;

    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $table = null;
}
