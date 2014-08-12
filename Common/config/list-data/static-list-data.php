<?php

return array(
    'bus_subsidy' => array(
        'bus_subsidy.no' => 'No',
        'bus_subsidy.yes' => 'Yes',
        'bus_subsidy.in_part' => 'In Part'
    ),
    'bus_trc_status' => array(
        'bus_trc_status.new' => 'New',
        'bus_trc_status.valid' => 'Valid',
        'bus_trc_status.revoked' => 'Revoked',
        'bus_trc_status.refused' => 'Refused'
    ),
    'case_categories_compliance' => array(
        'case_category.1' => 'Offences (inc. driver hours)',
        'case_category.2' => 'Prohibitions',
        'case_category.3' => 'Convictions',
        'case_category.4' => 'Penalties',
        'case_category.5' => 'ERRU MSI',
        'case_category.6' => 'Bus compliance',
        'case_category.7' => 'Section 9',
        'case_category.8' => 'Section 43',
        'case_category.9' => 'Impounding'
    ),
    'case_categories_bus' => array(
    ),
    'case_categories_tm' => array(
        'case_category.10' => 'Duplicate TM',
        'case_category.11' => 'Repute / professional competence of TM',
        'case_category.12' => 'TM Hours'
    ),
    'case_categories_app' => array(
        'case_category.13' => 'Interim with / without submission',
        'case_category.14' => 'Representation',
        'case_category.15' => 'Objection',
        'case_category.16' => 'Non-chargeable variation',
        'case_category.17' => 'Regulation 31/29',
        'case_category.18' => 'Schedule 4/1',
        'case_category.19' => 'Chargeable variation',
        'case_category.20' => 'New application'
    ),
    'case_categories_referral' => array(
        'case_category.21' => 'Surrender',
        'case_category.22' => 'Non application related maintenance issue',
        'case_category.23' => 'Review complaint',
        'case_category.24' => 'Late fee',
        'case_category.25' => 'Financial standing issue (continuation)',
        'case_category.26' => 'Repute fitness of director',
        'case_category.27' => 'Period of grace',
        'case_category.28' => 'In-Office revocation'
    ),
    'case_categories_bus' => array(
        'case_category.29' => 'Yes'
    ),
    'case_stay_outcome' => [
        'stay_status_granted' => 'Granted',
        'stay_status_refused' => 'Refused'
    ],
    'operator_locations' => [
        '0' => 'Great Britain',
        '1' => 'Northern Ireland'
    ],
    'operator_types' => [
        'lcat_gv' => 'Goods',
        'lcat_psv' => 'PSV'
    ],
    'licence_types' => [
        'ltyp_r' => 'Restricted',
        'ltyp_sn' => 'Standard National',
        'ltyp_si' => 'Standard International',
        'ltyp_sr' => 'Special Restricted',
    ],
    'business_types' =>
    [
        'org_t_rc' => 'Limited company',
        'org_t_st' => 'Sole Trader',
        'org_t_p' => 'Partnership',
        'org_t_llp' => 'Limited Liability Partnership',
        'org_t_pa' => 'Other (e.g. public authority, charity, trust, university)', // @todo No sure whether this is the correct ref data id
    ],
    'defendant_types' =>
    [
        'defendant_type.operator' => 'Operator',
        'defendant_type.owner' => 'Owner',
        'defendant_type.partner' => 'Partner',
        'defendant_type.director' => 'Director',
        'defendant_type.driver' => 'Driver',
        'defendant_type.transport_manager' => 'Transport Manager',
        'defendant_type.other' => 'Other'
    ],
    'yes_no' => [
        'Y' => 'Yes',
        'N' => 'No'
    ],
    'vehicle_body_types' =>
    [
        'vhl_body_type.flat' => 'Flat sided or skeletal',
        'vhl_body_type.box' => 'Box body, or van, or curtain side',
        'vhl_body_type.tanker' => 'Tanker',
        'vhl_body_type.other' => 'Other type (such as cement mixer, livestock carrier)'
    ],
    'statement_types' =>
    [
        'statement_type.1' => 'Section 43',
        'statement_type.2' => 'Section 9',
        'statement_type.3' => 'NI Section 43',
        'statement_type.4' => 'NI Section 9',
        'statement_type.5' => 'NI Section 36',
        'statement_type.6' => 'NI Section 38'
    ],
    'contact_type' =>
    [
        'contact_type.1' => 'Email',
        'contact_type.2' => 'Fax',
        'contact_type.3' => 'Letter',
        'contact_type.4' => 'Telephone'
    ],
    'countries' =>
    [
        'GB' => 'United Kingdom',
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua And Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia And Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo, Democratic Republic',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote D\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island & Mcdonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic Of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle Of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KR' => 'Korea',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People\'s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libyan Arab Jamahiriya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MK' => 'Macedonia',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States Of',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestinian Territory, Occupied',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy',
        'SH' => 'Saint Helena',
        'KN' => 'Saint Kitts And Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre And Miquelon',
        'VC' => 'Saint Vincent And Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome And Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia And Sandwich Isl.',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard And Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad And Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks And Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'US' => 'United States',
        'UM' => 'United States Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.S.',
        'WF' => 'Wallis And Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    ],
    'appeal_reasons' => [
        'appeal_reason.1' => 'Application',
        'appeal_reason.2' => 'Disciplinary PI',
        'appeal_reason.3' => 'Disciplinary Non PI',
        'appeal_reason.4' => 'Impounding'
    ],
    'appeal_outcomes' => [
        'appeal_outcome.1' => 'Successful',
        'appeal_outcome.2' => 'Partially Successful',
        'appeal_outcome.3' => 'Dismissed',
        'appeal_outcome.4' => 'Refer back to TC'
    ],
    'submission_recommendation' => [
        'submission_recommendation.other' => 'Other',
        'submission_recommendation.propose-to-revoke' => 'In-Office revocation',
        'submission_recommendation.warning-letter' => 'Warning letter',
        'submission_recommendation.nfa' => 'NFA',
        'submission_recommendation.undertakings-conditions' => 'Undertakings & conditions',
        'submission_recommendation.public-inquiry' => 'Public Inquiry',
        'submission_recommendation.preliminary-hearing' => 'Preliminary Hearing',
        'submission_recommendation.stl-interview' => 'STL Interview'
    ],
     'submission_decision' => [
        'submission_decision.agree' => 'Agree',
        'submission_decision.partially-agree' => 'Partially agree',
        'submission_decision.disagree' => 'Disagree',
        'submission_decision.further-info' => 'Further information required'
    ],
    'complaint_types' =>
    [
        'complaint_type.cor' => 'Continuing to operate after revocation',
        'complaint_type.cov' => 'Condition of vehicles',
        'complaint_type.dgm' => 'Driving in a dangerous manner',
        'complaint_type.dsk' => 'Driver smoking',
        'complaint_type.fls' => 'Failure to operate local service',
        'complaint_type.lvu' => 'Leaving vehicle unattended with engine running',
        'complaint_type.ndl' => 'Not having correct category of drivers licence',
        'complaint_type.nol' => 'No operators licence',
        'complaint_type.olr' => 'Operating local service off route',
        'complaint_type.ovb' => 'Obstructing other vehicles at bus station/bus stop',
        'complaint_type.pvo' => 'Parking vehicle out-with operating centre',
        'complaint_type.rds' => 'Registration of duplicate services',
        'complaint_type.rta' => 'Registered times not being adhered to',
        'complaint_type.sln' => 'Speed limiters non-operative',
        'complaint_type.spe' => 'Speeding',
        'complaint_type.tgo' => 'Tachograph offences',
        'complaint_type.ufl' => 'Unsafe loads',
        'complaint_type.ump' => 'Use of mobile phones while driving',
        'complaint_type.urd' => 'Using red diesel',
        'complaint_type.vpo' => 'Vehicles parked and causing an obstruction',
    ],
    'complaint_status_types' =>
    [
        'complaint_status.ack' => 'Acknowledged',
        'complaint_status.pin' => 'PI Notififed',
        'complaint_status.rfs' => 'Review Form Sent',
        'complaint_status.vfr' => 'Valid For Review',
        'complaint_status.yst' => 'Are you still there'
    ],
    'inspection_interval_vehicle' => [
        'inspection_interval_vehicle.1' => '1 {Week}',
        'inspection_interval_vehicle.2' => '2 {Weeks}',
        'inspection_interval_vehicle.3' => '3 {Weeks}',
        'inspection_interval_vehicle.4' => '4 {Weeks}',
        'inspection_interval_vehicle.5' => '5 {Weeks}',
        'inspection_interval_vehicle.6' => '6 {Weeks}',
        'inspection_interval_vehicle.7' => '7 {Weeks}',
        'inspection_interval_vehicle.8' => '8 {Weeks}',
        'inspection_interval_vehicle.9' => '9 {Weeks}',
        'inspection_interval_vehicle.10' => '10 {Weeks}',
        'inspection_interval_vehicle.11' => '11 {Weeks}',
        'inspection_interval_vehicle.12' => '12 {Weeks}',
        'inspection_interval_vehicle.13' => '13 {Weeks}'
    ],
    'inspection_interval_trailer' => [
        'inspection_interval_trailer.1' => '1 {Week}',
        'inspection_interval_trailer.2' => '2 {Weeks}',
        'inspection_interval_trailer.3' => '3 {Weeks}',
        'inspection_interval_trailer.4' => '4 {Weeks}',
        'inspection_interval_trailer.5' => '5 {Weeks}',
        'inspection_interval_trailer.6' => '6 {Weeks}',
        'inspection_interval_trailer.7' => '7 {Weeks}',
        'inspection_interval_trailer.8' => '8 {Weeks}',
        'inspection_interval_trailer.9' => '9 {Weeks}',
        'inspection_interval_trailer.10' => '10 {Weeks}',
        'inspection_interval_trailer.11' => '11 {Weeks}',
        'inspection_interval_trailer.12' => '12 {Weeks}',
        'inspection_interval_trailer.13' => '13 {Weeks}',
        'inspection_interval_trailer.0' => 'N/A'
    ],
    'tachograph_analyser' => [
        'tach_internal' => 'tachographAnalyser-yourself',
        'tach_external' => 'tachographAnalyser-external-contractor',
        'tach_na' => 'N/A',
    ],
    'impounding_type' => [
        'impounding_type.1' => 'Hearing',
        'impounding_type.2' => 'Paperwork only'
    ],
    'impounding_outcome' => [
        'impounding_outcome.1' => 'Vehicle(s) returned',
        'impounding_outcome.2' => 'Vehicle(s) not returned',
    ],
    'hearing_location' => [
        'hearing_location.1' => 'Hearing location 1',
        'hearing_location.2' => 'Hearing location 2',
        'hearing_location.3' => 'Hearing location 3',
    ],
    'presiding_tc' => [
        'presiding_tc.1' => 'Presiding TC 1',
        'presiding_tc.2' => 'Presiding TC 2',
        'presiding_tc.3' => 'Presiding TC 3',
    ]

);
