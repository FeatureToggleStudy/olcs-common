<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("licenceChecklistConfirmationNo")
 */
class LicenceChecklistConfirmationNo
{
    /**
     * @Form\Attributes({"value": "markup-continuation-licence-checklist-confirmation-no"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $checklistDeclineText = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--tertiary large"})
     * @Form\Options({"label": "continuations.checklist.confirmation.no-button"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $backToLicence = null;
}
