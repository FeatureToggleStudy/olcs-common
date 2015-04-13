<?php

namespace Common\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-transport-manager-details")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class TransportManagerDetails
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\Details")
     */
    public $details = null;

    /**
     * @Form\Name("homeAddress")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({"label":"lva-tm-details-details-homeAddress"})
     */
    public $homeAddress = null;

    /**
     * @Form\Name("workAddress")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({"label":"lva-tm-details-details-workAddress"})
     */
    public $workAddress = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TmDetailsFormActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}