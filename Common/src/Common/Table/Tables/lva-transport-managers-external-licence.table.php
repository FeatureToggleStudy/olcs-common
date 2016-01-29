<?php

return array(
    'variables' => array(
        'title' => 'Transport Managers',
        'within_form' => true,
        'empty_message' => 'lva-transport-manager-table-empty-message'
    ),
    'settings' => array(
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Name',
            'formatter' => 'TransportManagerName',
            'internal' => false,
            'lva' => 'licence',
        ),
        array(
            'title' => 'Email',
            'name' => 'email'
        ),
        array(
            'title' => 'Date of birth',
            'name' => 'dob',
            'formatter' => 'Date',
        ),
    )
);
