<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * Licence History Eu
 */
class LicenceHistoryEu
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "application_previous-history_licence-history_prevBeenRefused",
     *     "value_options": {
     *         {
     *             "value": "Y",
     *             "label": "Yes",
     *             "label_attributes": {
     *                 "aria-label": "Does anyone you've named already have an operator's licence in any traffic area? Yes",
     *                 "class" : "inline"
     *             }
     *         },
     *         {
     *             "value": "N",
     *             "label": "No",
     *             "label_attributes": {
     *                 "class" : "inline"
     *             }
     *         }
     *     },
     *     "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *     }
     * })
     * @Form\Type("radio")
     * @Form\Validator({
     *     "name":"Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     "options": {"table": "prevBeenRefused-table"}
     *})
     */
    public $prevBeenRefused = null;

    /**
     * @Form\Name("prevBeenRefused-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $prevBeenRefusedTable = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "application_previous-history_licence-history_prevBeenRevoked",
     *     "value_options": {
     *         {
     *             "value": "Y",
     *             "label": "Yes",
     *             "label_attributes": {
     *                 "aria-label": "Has anyone you've named ever had an operator's licence application revoked, suspended or curtailed in the European Union? Yes",
     *                 "class" : "inline"
     *             }
     *         },
     *         {
     *             "value": "N",
     *             "label": "No",
     *             "label_attributes": {
     *                 "class" : "inline"
     *             }
     *         }
     *     },
     *     "fieldset-attributes" : {
     *          "class":"checkbox inline"
     *     }
     * })
     * @Form\Type("radio")
     * @Form\Validator({
     *     "name":"Common\Form\Elements\Validators\LicenceHistoryLicenceValidator",
     *     "options": {"table": "prevBeenRevoked-table"}
     *})
     */
    public $prevBeenRevoked = null;

    /**
     * @Form\Name("prevBeenRevoked-table")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     */
    public $prevBeenRevokedTable = null;
}
