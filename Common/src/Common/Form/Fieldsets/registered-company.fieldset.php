<?php
return
[    
    'name' => 'registered-company',
    'elements' => 
    [
        'company_number' => 
        [
            'label' => 'Registered company number',
            'type' => 'companyNumber',
        ],
        'submit_lookup_company' => 
        [
            'name' => 'submit_lookup_company',
            'value' => 'lookup_company',
            'type' => 'submit',
            'label' => 'Find'
        ],
        'company_name' => 
        [
            'type' => 'companyName',
        ],
        'trading_names' => 
        [
            'type' => 'tradingNames',              
        ],
        'submit_add_trading_name' => 
        [
            'name' => 'submit_add_trading_name',
            'value' => 'add_trading_name',
            'type' => 'submit',
            'label' => 'Add another',
            // 'class' => 'field inline',
        ],
        'type_of_business' => 
        [
            'type' => 'businessType',
            'value_options' => 'sic_codes',             
        ],
    ],
    'options' =>
    [
        'next_step' =>
        [
            'default' => 'complete',
        ]
    ],
];