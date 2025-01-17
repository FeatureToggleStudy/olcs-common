<?php

namespace Common\Form\Model\Form\Lva;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles")
 * @Form\Attributes({"method":"post", "class":"table__form"})
 * @Form\Type("Common\Form\Form")
 */
class GoodsVehicles
{
    /**
     * @Form\Name("query")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesQuery")
     * @Form\Attributes({
     *   "class": "visually-hidden"
     * })
     */
    public $query = null;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\VehiclesData")
     */
    public $data = null;

    /**
     * @Form\Name("table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *   "class": "table"
     * })
     */
    public $table = null;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\ShareInfo")
     */
    public $shareInfo = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"actions-container"})
     */
    public $formActions = null;
}
