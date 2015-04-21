<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv-small.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'vehicle',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.vrm',
            'name' => 'vrm',
            'action' => 'edit',
            'type' => 'Action',
        ),
        array(
            'title' => $translationPrefix . '.make',
            'name' => 'makeModel'
        ),
        array(
            'title' => $translationPrefix . '.novelty',
            'name' => 'isNovelty',
            'formatter' => 'YesNo'
        ),
        array(
            'title' => $translationPrefix . '.specified',
            'formatter' => 'Date',
            'name' => 'specifiedDate'
        ),
        array(
            'title' => $translationPrefix . '.removed',
            'formatter' => 'Date',
            'name' => 'deletedDate'
        ),
        array(
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
