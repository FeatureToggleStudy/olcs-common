<?php

$gb = include(__DIR__ . '/en_GB.php');

$ni = array_merge(
    $gb,
    [
        'TM1_FORM_LINK' => 'https://www.infrastructure-ni.gov.uk/sites/default/files/publications/doe/tm%28ni%291-application-to-add-a-transport-manager-to-a-goods-vehicle-operator%27s-licence.pdf',
        'selfserve-app-subSection-previous-history-criminal-conviction-hasConv' => 'Has any person named in this
            application (including partners, directors and Transport Managers); any company of which a person named on
            this application is or has been a director; any parent company if you are a limited company; received any
            penalties or have currently any unspent convictions?',
        'selfserve-app-subSection-previous-history-criminal-conviction-tableEmptyMessage' => 'Please add any relevant
            offences which must be declared to the DfI.',
        'selfserve-app-subSection-previous-history-criminal-conviction-labelConfirm' => 'Please tick to confirm that you
            are aware that you must tell the DfI immediately of any relevant convictions that occur between the
            submission of your application and a decision being made on this application.',
        'application_previous-history_licence-history_prevHasLicence' => 'Does anyone you\'ve named already have an operator\'s licence in Great Britain or Northern Ireland?',
        'application_previous-history_licence-history_prevHadLicence' => 'Has anyone you\'ve named ever had or applied for an operator\'s licence in Great Britain or Northern Ireland?',
        'application_previous-history_licence-history_prevBeenDisqualifiedTc' => 'Has anyone you\'ve named ever been disqualified from having an operator\'s licence in Great Britain or Northern Ireland?',
        'application_previous-history_licence-history_prevBeenAtPi' => 'Has anyone you\'ve named ever taken part in a public inquiry held by the DfI Northern Ireland or a GB Traffic Commissioner?',
        'application_previous-history_licence-history_prevPurchasedAssets' => 'In the past 12 months, has anyone you\'ve named bought assets or shares in a company that holds or has held an operator\'s licence in Great Britain or Northern Ireland?',
        'safety-inspection-providers.table.hint' => 'See the <a href="https://www.infrastructure-ni.gov.uk/sites/default/files/publications/doe/driver-vehicle-agency-safe-operators-guide-march-2016.pdf" target="_blank">safe operator\'s</a> guide for more information.',
        'transport-manager.responsibilities.additional-information' => 'If you will spend less than the <a href="https://www.infrastructure-ni.gov.uk/sites/default/files/publications/doe/tm-ni-1g-guidance-notes-for-an-application-to-add-a-transport-manager-to-a-goods-vehicle-operator%27s-licence.pdf" target="black" rel="external">recommended amount of time</a> on your Transport Manager duties for the relevant licence(s) you need to tell us why. Your explanation should include details of driver, vehicle and licence administration, management and operations, compliance systems, and any relevant knowledge and skills you possess. If you have a document prepared you can upload it below.',
    ]
);

return $ni;
