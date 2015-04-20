<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":""})
 * @Form\Name("tm-employment-details")
 */
class EmploymentDetails
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
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"transport-manager.employment.form.position",
     *     "short-label":"transport-manager.employment.form.position"
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     */
    public $position = null;

    /**
     * @Form\Attributes({"class":"long"})
     * @Form\Options({
     *     "label":"transport-manager.employment.form.hoursPerWeek",
     *     "short-label":"transport-manager.employment.form.hoursPerWeek"
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name": "\Zend\Validator\NotEmpty"})
     */
    public $hoursPerWeek = null;
}