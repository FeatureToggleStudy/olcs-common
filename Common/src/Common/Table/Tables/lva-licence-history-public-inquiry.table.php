<?php
$prefix = 'selfserve-app-subSection-previous-history-previous-licence-';

return array(
    'variables' => array(
        'title' => $prefix . 'tableHeader',
        'within_form' => true,
        'empty_message' => $prefix . 'tableEmptyMessage'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'public-inquiry',
            'actions' => array(
                'add' => array('class' => 'primary', 'label' => 'Add licence'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true, 'label' => 'Remove')
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
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
