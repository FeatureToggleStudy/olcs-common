<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title',
        'titleSingular' => $translationPrefix . '.title.singular',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'vehicle',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array(
                    'id' => 'addSmall'
                ),
                'delete' => array(
                    'label' => 'action_links.remove',
                    'class' => ' more-actions__item',
                    'requireRows' => true
                ),
                'transfer' => array(
                    'label' => 'Transfer',
                    'class' => ' more-actions__item js-require--multiple',
                    'requireRows' => true,
                    'id' => 'transferSmall'
                )
            )
        ),
        'row-disabled-callback' => function ($row) {
            return $row['removalDate'] !== null;
        },
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        ),
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
        'collapseAt' => 1
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.vrm',
            'stack' => 'vehicle->vrm',
            'formatter' => 'StackValue',
            'action' => 'edit',
            'type' => 'Action',
            'sort' => 'v.vrm',
            'keepForReadOnly' => true
        ),
        array(
            'title' => $translationPrefix . '.make',
            'stack' => 'vehicle->makeModel',
            'formatter' => 'StackValue'
        ),
        array(
            'title' => $translationPrefix . '.specified',
            'name' => 'specifiedDate',
            'formatter' => 'Date',
            'sort' => 'specifiedDate'
        ),
        array(
            'title' => $translationPrefix . '.removed',
            'name' => 'removalDate',
            'formatter' => 'Date',
            'sort' => 'removalDate'
        ),
        array(
            'type' => 'ActionLinks',
            'isRemoveVisible' => function ($data) {
                return empty($data['removalDate']);
            },
            'deleteInputName' => 'vehicles[action][delete][%d]'
        ),
        array(
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true
        )
    )
);
