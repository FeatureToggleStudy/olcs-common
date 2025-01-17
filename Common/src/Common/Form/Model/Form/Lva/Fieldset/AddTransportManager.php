<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Add Transport Manager fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddTransportManager
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "transport-manager-choose"
     * })
     * @Form\Type("Select")
     */
    public $registeredUser = null;

    /**
     * @Form\Attributes({"id":"addUser","type":"submit","class":""})
     * @Form\Options({"label": "transport-manager-add"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $addUser = null;
}
