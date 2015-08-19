<?php

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\FormService\Form\Lva\BusinessDetails;

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetails extends AbstractBusinessDetails
{
    protected function alterForm($form, $params)
    {
        $this->getFormServiceLocator()->get('lva-variation')->alterForm($form);

        parent::alterForm($form, $params);
    }
}
