<?php

$translationPrefix = 'safety-inspection-providers.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'safety inspection provider',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'label' => 'Add safety inspector'),
            )
        )
    ),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.providerName',
            'action' => 'edit',
            'stack' => 'contactDetails->fao',
            'formatter' => 'StackValue',
            'type' => 'Action'
        ),
        array(
            'title' => $translationPrefix . '.external',
            'name' => 'isExternal',
            'formatter' => 'YesNo'
        ),
        array(
            'title' => $translationPrefix . '.address',
            'formatter' => 'Address',
            'name' => 'contactDetails->address'
        ),
        array(
            'type' => 'ActionLinks',
        ),
    )
);
