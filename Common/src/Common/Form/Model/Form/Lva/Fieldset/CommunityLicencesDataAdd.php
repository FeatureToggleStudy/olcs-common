<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("community-licences-data-add")
 */
class CommunityLicencesDataAdd
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"short","id":"","required":false})
     * @Form\Options({"label":"application.community_licence.form.total_community_licences"})
     * @Form\Validator({"name": "Digits"})
     * @Form\Validator({
     *  "name": "GreaterThan",
     *  "options": {"min":0, "messages": {"notGreaterThan": "community-licences.add.error-message.min"}}
     * })
     * @Form\Validator({
     *  "name": "LessThan",
     *  "options": {"max":51, "messages": {"notLessThan": "community-licences.add.error-message.max"}}
     * })
     * @Form\Type("Text")
     */
    public $total = null;
}
