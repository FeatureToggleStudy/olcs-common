<?php

namespace Common\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("vehicles-transfer")
 * @Form\Attributes({"method":"post", "class":"js-modal-alert"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesTransfer
{
    /**
     * @Form\Name("data")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesTransferDetails")
     */
    public $data = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesTransferActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
