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
            'formName' => 'current',
            'actions' => array(
                'add' => array('label' => 'Add licence'),
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
            'title' => $prefix . 'columnWillSurrender',
            'name' => 'willSurrender',
            'formatter' => 'YesNo'
        ),
        array(
            'type' => 'ActionLinks',
            'deleteInputName' => 'data[prevHasLicence-table][action][delete][%d]'
        )
    )
);
