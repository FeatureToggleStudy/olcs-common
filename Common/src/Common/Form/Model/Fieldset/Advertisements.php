<?php

namespace Common\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("advertisements")
 * @Form\Options({
 *     "label":
 * "application_operating-centres_authorisation-sub-action.advertisements"
 * })
 */
class Advertisements
{

    /**
     * @Form\Attributes({"id":"adPlaced","placeholder":""})
     * @Form\Options({
     *     "label":
     * "application_operating-centres_authorisation-sub-action.advertisements.adPlaced",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "help-block": "Please choose",
     *     "label_attributes": {
     *         "class": "inline"
     *     }
     * })
     * @Form\Type("\Zend\Form\Element\Radio")
     */
    public $adPlaced = null;

    /**
     * @Form\Attributes({"class":"","id":"adPlacedIn"})
     * @Form\Options({
     *     "label":
     * "application_operating-centres_authorisation-sub-action.advertisements.adPlacedIn"
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\OperatingCentreAdPlacedIn")
     */
    public $adPlacedIn = null;

    /**
     * @Form\Attributes({"id":"adPlacedDate"})
     * @Form\Options({
     *     "label":
     * "application_operating-centres_authorisation-sub-action.advertisements.adPlacedDate",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\OperatingCentreDateAdPlaced")
     */
    public $adPlacedDate = null;

    /**
     * @Form\Attributes({"id":"file", "class": "file-upload"})
     * @Form\Options({
     *     "label":
     * "application_operating-centres_authorisation-sub-action.advertisements.file",
     *     "hint":
     * "application_operating-centres_authorisation-sub-action.advertisements.file.hint"
     * })
     * @Form\Type("\Common\Form\Elements\Types\MultipleFileUpload")
     */
    public $file = null;


}

