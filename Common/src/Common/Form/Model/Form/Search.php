<?php

namespace Common\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("search")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class Search
{
    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label":"Search"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;

    /**
     * @Form\Name("search")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Search")
     */
    public $search = null;

    /**
     * @Form\Name("advanced")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Advanced")
     * @Form\Options({"label":"Advanced search"})
     */
    public $advanced = null;
}
