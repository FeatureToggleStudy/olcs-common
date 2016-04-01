<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.otherlicences.table',
        'within_form' => true,
        'empty_message' => 'transport-manager.otherlicences.table.empty',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add-other-licence-applications' => array('label' => 'transport-manager.otherlicences.table.add', 'class' => 'primary'),
            ),
        ),
    ),
    'columns' => array(
        array(
            'title' => 'transport-manager.otherlicences.table.lic_no',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit-other-licence-applications'
        ),
        array(
            'title' => 'transport-manager.otherlicences.table.role',
            'name' => 'role',
            'formatter' => 'RefData'
        ),
        array(
            'title' => 'transport-manager.otherlicences.table.total_auth_vehicles',
            'name' => 'totalAuthVehicles',
        ),
        array(
            'title' => 'transport-manager.otherlicences.table.hours_per_week',
            'name' => 'hoursPerWeek',
        ),
        array(
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-other-licence-applications][%d]'
        )
    )
);
