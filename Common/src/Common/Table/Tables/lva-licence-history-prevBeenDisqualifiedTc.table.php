<?php
$prefix = 'selfserve-app-subSection-previous-history-previous-licence-';

return array(
    'variables' => array(
        'title' => $prefix . 'tableHeader',
        'within_form' => true,
        'empty_message' => 'application_previous-history_licence-history_table_empty'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'disqualified',
            'actions' => array(
                'add' => array('class' => 'primary', 'label' => 'Add licence'),
            )
        )
    ),
    'columns' => array(
        array(
            'title' => $prefix . 'columnLicNo',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit'
        ),
        array(
            'title' => $prefix . 'columnHolderName',
            'name' => 'holderName',
        ),
        array(
            'title' => $prefix . 'columnDisqualificationDate',
            'name' => 'disqualificationDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $prefix . 'columnDisqualificationLength',
            'name' => 'disqualificationLength',
        ),
        array(
            'type' => 'ActionLinks',
            'deleteInputName' => 'data[prevBeenDisqualifiedTc-table][action][delete][%d]'
        )
    )
);
