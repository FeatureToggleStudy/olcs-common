<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("evidence")
 * @Form\Attributes({"class":"last"})
 */
class FinancialEvidenceEvidence
{
    /**
     * @Form\Options({
     *     "legend-attributes": {"class": "visually-hidden",},
     *     "label": "lva-financial-evidence-upload-now.label",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options": {
     *         "1":"lva-financial-evidence-upload-now.yes",
     *         "0":"lva-financial-evidence-upload-now.no",
     *         "2":"lva-financial-evidence-upload-now.later"
     *     },
     *     "error-message": "financialEvidence_uploadNow-error"
     * })
     * @Form\Type("\Zend\Form\Element\Radio")
     */
    public $uploadNow = null;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Fieldset\MultipleFileUpload")
     * @Form\Attributes({"id":"files"})
     */
    public $files = null;

    /**
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "fieldset-attributes": {
     *         "id": "files",
     *     },
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required":false, "id":"uploadedFileCount"})
     * @Form\Type("Hidden")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "uploadNow",
     *          "context_values": {"1"},
     *          "validators": {
     *              {
     *                  "name": "\Common\Validator\FileUploadCount",
     *                  "options": {
     *                      "min": 1,
     *                      "message": "lva-financial-evidence-upload.required"
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $uploadedFileCount = null;
}
