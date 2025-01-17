<?php

namespace Common\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("lva-add-transport-manager")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class AddTransportManager
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\AddTransportManager")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ContinueFormActions")
     */
    public $formActions = null;
}
