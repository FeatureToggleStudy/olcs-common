<?php

return array(
    'variables' => array(
        'title' => '',
        'within_form' => true,
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Name',
            'formatter' => 'TransportManagerName',
            'internal' => false,
            'lva' => 'application',
        ),
        array(
            'title' => 'Email',
            'name' => 'email'
        ),
        array(
            'title' => 'DOB',
            'name' => 'dob',
            'formatter' => 'Date',
        ),
        array(
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);