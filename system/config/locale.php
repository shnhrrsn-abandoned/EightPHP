<?php
/**
 * @package		System
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @link http://php.net/setlocale
 */
$config['language'] = array('en_US', 'English_United States');

/**
 * Locale timezone. Defaults to use the server timezone.
 * @see http://php.net/timezones
 */
$config['timezone'] = 'America/New_York';

/**
 * Time Zone List
 */
$config['timezones'] = array(
	'US/Hawaii',
	'US/Alaska',
	'US/Pacific',
	'US/Arizona',
	'US/Mountain',
	'US/Central',
	'US/Eastern',
	'US/East-Indiana',
	'America/Porto_Acre',
	'America/Eirunepe',
	'America/Rio_Branco',
	'Brazil/Acre',
	'America/Goose_Bay',
	'America/Pangnirtung',
	'America/Halifax',
	'America/Barbados',
	'America/Blanc-Sablon',
	'America/Glace_Bay',
	'America/Martinique',
	'America/Moncton',
	'America/Thule',
	'Atlantic/Bermuda',
	'Canada/Atlantic',
	'Asia/Baghdad',
	'Asia/Kabul',
	'America/Anchorage',
	'US/Alaska',
	'America/Adak',
	'America/Atka',
	'US/Aleutian',
	'America/Juneau',
	'America/Nome',
	'America/Yakutat',
	'Asia/Aqtobe',
	'Asia/Almaty',
	'Asia/Yerevan',
	'America/Boa_Vista',
	'America/Campo_Grande',
	'America/Cuiaba',
	'America/Manaus',
	'America/Porto_Velho',
	'Brazil/West',
	'Europe/Amsterdam',
	'Asia/Anadyr',
	'America/Curacao',
	'America/Aruba',
	'America/Puerto_Rico',
	'Asia/Aqtau',
	'America/Buenos_Aires',
	'America/Argentina/Buenos_Aires',
	'America/Argentina/Catamarca',
	'America/Argentina/ComodRivadavia',
	'America/Argentina/Cordoba',
	'America/Argentina/Jujuy',
	'America/Argentina/La_Rioja',
	'America/Argentina/Mendoza',
	'America/Argentina/Rio_Gallegos',
	'America/Argentina/San_Juan',
	'America/Argentina/Tucuman',
	'America/Argentina/Ushuaia',
	'America/Catamarca',
	'America/Cordoba',
	'America/Jujuy',
	'America/Mendoza',
	'America/Rosario',
	'Antarctica/Palmer',
	'Asia/Ashkhabad',
	'Asia/Ashgabat',
	'Asia/Riyadh',
	'America/Anguilla',
	'America/Antigua',
	'America/Dominica',
	'America/Grenada',
	'America/Guadeloupe',
	'America/Miquelon',
	'America/Montserrat',
	'America/Port_of_Spain',
	'America/Santo_Domingo',
	'America/St_Kitts',
	'America/St_Lucia',
	'America/St_Thomas',
	'America/St_Vincent',
	'America/Tortola',
	'America/Virgin',
	'Asia/Aden',
	'Asia/Bahrain',
	'Asia/Kuwait',
	'Asia/Qatar',
	'Atlantic/Azores',
	'Asia/Baku',
	'Europe/London',
	'Europe/Belfast',
	'Europe/Gibraltar',
	'Europe/Guernsey',
	'Europe/Isle_of_Man',
	'Europe/Jersey',
	'GB',
	'GB-Eire',
	'Asia/Dacca',
	'Asia/Dhaka',
	'Africa/Mogadishu',
	'Africa/Kampala',
	'Africa/Nairobi',
	'Africa/Dar_es_Salaam',
	'Africa/Banjul',
	'Europe/Tiraspol',
	'Europe/Chisinau',
	'Asia/Brunei',
	'Asia/Kuching',
	'America/La_Paz',
	'America/Sao_Paulo',
	'America/Araguaina',
	'America/Bahia',
	'America/Belem',
	'America/Fortaleza',
	'America/Maceio',
	'America/Recife',
	'Brazil/East',
	'Pacific/Midway',
	'Pacific/Pago_Pago',
	'Pacific/Samoa',
	'US/Samoa',
	'Eire',
	'Europe/Dublin',
	'Asia/Thimbu',
	'Asia/Thimphu',
	'Asia/Calcutta',
	'Asia/Rangoon',
	'Atlantic/Canary',
	'Australia/Adelaide',
	'Africa/Gaborone',
	'Africa/Khartoum',
	'Africa/Blantyre',
	'Africa/Harare',
	'Africa/Kigali',
	'Africa/Lusaka',
	'Africa/Maputo',
	'Africa/Windhoek',
	'America/Rankin_Inlet',
	'America/Chicago',
	'America/Havana',
	'Cuba',
	'America/Atikokan',
	'America/Belize',
	'America/Cambridge_Bay',
	'America/Cancun',
	'America/Chihuahua',
	'America/Coral_Harbour',
	'America/Costa_Rica',
	'America/El_Salvador',
	'America/Fort_Wayne',
	'America/Guatemala',
	'America/Indiana/Indianapolis',
	'America/Indiana/Knox',
	'America/Indiana/Marengo',
	'America/Indiana/Petersburg',
	'America/Indiana/Vevay',
	'America/Indiana/Vincennes',
	'America/Indiana/Winamac',
	'America/Indianapolis',
	'America/Iqaluit',
	'America/Kentucky/Louisville',
	'America/Kentucky/Monticello',
	'America/Knox_IN',
	'America/Louisville',
	'America/Managua',
	'America/Menominee',
	'America/Merida',
	'America/Mexico_City',
	'America/Monterrey',
	'America/North_Dakota/Center',
	'America/North_Dakota/New_Salem',
	'America/Rainy_River',
	'America/Tegucigalpa',
	'America/Winnipeg',
	'Canada/Central',
	'CST6CDT',
	'Mexico/General',
	'US/Central',
	'US/East-Indiana',
	'US/Indiana-Starke',
	'Asia/Shanghai',
	'Asia/Chongqing',
	'Asia/Chungking',
	'Asia/Harbin',
	'Asia/Kashgar',
	'Asia/Taipei',
	'Asia/Urumqi',
	'PRC',
	'ROC',
	'Europe/Berlin',
	'CET',
	'Europe/Kaliningrad',
	'Africa/Algiers',
	'Africa/Ceuta',
	'Africa/Tripoli',
	'Africa/Tunis',
	'Arctic/Longyearbyen',
	'Atlantic/Jan_Mayen',
	'Europe/Andorra',
	'Europe/Athens',
	'Europe/Belgrade',
	'Europe/Bratislava',
	'Europe/Brussels',
	'Europe/Budapest',
	'Europe/Copenhagen',
	'Europe/Kiev',
	'Europe/Lisbon',
	'Europe/Ljubljana',
	'Europe/Luxembourg',
	'Europe/Madrid',
	'Europe/Malta',
	'Europe/Minsk',
	'Europe/Monaco',
	'Europe/Oslo',
	'Europe/Paris',
	'Europe/Podgorica',
	'Europe/Prague',
	'Europe/Riga',
	'Europe/Rome',
	'Europe/San_Marino',
	'Europe/Sarajevo',
	'Europe/Simferopol',
	'Europe/Skopje',
	'Europe/Sofia',
	'Europe/Stockholm',
	'Europe/Tallinn',
	'Europe/Tirane',
	'Europe/Uzhgorod',
	'Europe/Vaduz',
	'Europe/Vatican',
	'Europe/Vienna',
	'Europe/Vilnius',
	'Europe/Warsaw',
	'Europe/Zagreb',
	'Europe/Zaporozhye',
	'Europe/Zurich',
	'Libya',
	'Poland',
	'Portugal',
	'WET',
	'Africa/Casablanca',
	'America/Scoresbysund',
	'Pacific/Chatham',
	'NZ-CHAT',
	'Asia/Choibalsan',
	'Asia/Dili',
	'Asia/Makassar',
	'Asia/Pontianak',
	'Asia/Ujung_Pandang',
	'Asia/Sakhalin',
	'Pacific/Rarotonga',
	'America/Santiago',
	'Chile/Continental',
	'America/Bogota',
	'America/Detroit',
	'America/Hermosillo',
	'America/Mazatlan',
	'America/Regina',
	'America/Swift_Current',
	'Canada/East-Saskatchewan',
	'Canada/Saskatchewan',
	'Mexico/BajaSur',
	'US/Michigan',
	'Asia/Macao',
	'Asia/Macau',
	'Asia/Jayapura',
	'Australia/Broken_Hill',
	'Australia/Darwin',
	'Australia/North',
	'Australia/South',
	'Australia/Yancowinna',
	'Atlantic/Cape_Verde',
	'Australia/Eucla',
	'Pacific/Guam',
	'Pacific/Saipan',
	'Antarctica/Davis',
	'Antarctica/DumontDUrville',
	'Asia/Dushanbe',
	'Chile/EasterIsland',
	'Pacific/Easter',
	'Indian/Antananarivo',
	'Africa/Addis_Ababa',
	'Africa/Asmara',
	'Africa/Asmera',
	'Africa/Djibouti',
	'Indian/Comoro',
	'Indian/Mayotte',
	'America/Guayaquil',
	'Pacific/Galapagos',
	'America/New_York',
	'America/Grand_Turk',
	'America/Jamaica',
	'America/Montreal',
	'America/Nassau',
	'America/Nipigon',
	'America/Port-au-Prince',
	'America/Thunder_Bay',
	'America/Toronto',
	'Canada/Eastern',
	'EST',
	'EST5EDT',
	'Jamaica',
	'US/Eastern',
	'Europe/Helsinki',
	'Africa/Cairo',
	'Asia/Amman',
	'Asia/Beirut',
	'Asia/Damascus',
	'Asia/Gaza',
	'Asia/Istanbul',
	'Asia/Nicosia',
	'EET',
	'Egypt',
	'Europe/Bucharest',
	'Europe/Istanbul',
	'Europe/Mariehamn',
	'Europe/Moscow',
	'Europe/Nicosia',
	'Turkey',
	'W-SU',
	'America/Cayman',
	'America/Panama',
	'Australia/ACT',
	'Australia/Brisbane',
	'Australia/Canberra',
	'Australia/Currie',
	'Australia/Hobart',
	'Australia/Lindeman',
	'Australia/Melbourne',
	'Australia/NSW',
	'Australia/Queensland',
	'Australia/Sydney',
	'Australia/Tasmania',
	'Australia/Victoria',
	'Pacific/Fiji',
	'Atlantic/Stanley',
	'America/Noronha',
	'Brazil/DeNoronha',
	'Asia/Bishkek',
	'Pacific/Gambier',
	'America/Guyana',
	'Asia/Tbilisi',
	'America/Cayenne',
	'Africa/Accra',
	'Africa/Abidjan',
	'Africa/Bamako',
	'Africa/Bissau',
	'Africa/Conakry',
	'Africa/Dakar',
	'Africa/Freetown',
	'Africa/Malabo',
	'Africa/Monrovia',
	'Africa/Niamey',
	'Africa/Nouakchott',
	'Africa/Ouagadougou',
	'Africa/Porto-Novo',
	'Africa/Sao_Tome',
	'Africa/Timbuktu',
	'America/Danmarkshavn',
	'Atlantic/Reykjavik',
	'Atlantic/St_Helena',
	'Iceland',
	'Asia/Dubai',
	'Asia/Muscat',
	'Pacific/Honolulu',
	'HST',
	'US/Hawaii',
	'Asia/Hong_Kong',
	'Hongkong',
	'Asia/Hovd',
	'Asia/Bangkok',
	'Asia/Phnom_Penh',
	'Asia/Saigon',
	'Asia/Vientiane',
	'Asia/Jerusalem',
	'Asia/Tel_Aviv',
	'Israel',
	'Asia/Colombo',
	'Indian/Chagos',
	'Asia/Tehran',
	'Iran',
	'Asia/Irkutsk',
	'Asia/Karachi',
	'Asia/Katmandu',
	'Asia/Jakarta',
	'Asia/Tokyo',
	'Japan',
	'Asia/Kuala_Lumpur',
	'Asia/Manila',
	'Asia/Singapore',
	'Pacific/Nauru',
	'Singapore',
	'Asia/Seoul',
	'ROK',
	'Asia/Qyzylorda',
	'Pacific/Kosrae',
	'Asia/Krasnoyarsk',
	'Asia/Pyongyang',
	'Europe/Samara',
	'Pacific/Kwajalein',
	'Kwajalein',
	'Australia/Lord_Howe',
	'Australia/LHI',
	'Pacific/Kiritimati',
	'Atlantic/Madeira',
	'Asia/Magadan',
	'Pacific/Marquesas',
	'Antarctica/Mawson',
	'America/Yellowknife',
	'America/Denver',
	'America/Boise',
	'America/Edmonton',
	'America/Inuvik',
	'America/Phoenix',
	'America/Shiprock',
	'Canada/Mountain',
	'MST',
	'MST7MDT',
	'Navajo',
	'US/Arizona',
	'US/Mountain',
	'MET',
	'Pacific/Majuro',
	'America/Dawson_Creek',
	'America/Ensenada',
	'America/Tijuana',
	'Mexico/BajaNorte',
	'Indian/Mauritius',
	'Indian/Maldives',
	'Pacific/Noumea',
	'America/St_Johns',
	'Canada/Newfoundland',
	'America/Paramaribo',
	'Pacific/Norfolk',
	'Asia/Novosibirsk',
	'Pacific/Niue',
	'Pacific/Auckland',
	'Antarctica/McMurdo',
	'Antarctica/South_Pole',
	'NZ',
	'Asia/Omsk',
	'Asia/Oral',
	'America/Los_Angeles',
	'America/Dawson',
	'America/Vancouver',
	'America/Whitehorse',
	'Canada/Pacific',
	'Canada/Yukon',
	'PST8PDT',
	'US/Pacific',
	'America/Lima',
	'Asia/Kamchatka',
	'Pacific/Enderbury',
	'Pacific/Pitcairn',
	'America/Asuncion',
	'Indian/Reunion',
	'Antarctica/Rothera',
	'Asia/Samarkand',
	'Pacific/Apia',
	'Africa/Johannesburg',
	'Africa/Maseru',
	'Africa/Mbabane',
	'Pacific/Guadalcanal',
	'Indian/Mahe',
	'Europe/Volgograd',
	'Asia/Yekaterinburg',
	'Antarctica/Syowa',
	'Pacific/Tahiti',
	'Asia/Tashkent',
	'Indian/Kerguelen',
	'Pacific/Tongatapu',
	'Asia/Ulaanbaatar',
	'Asia/Ulan_Bator',
	'America/Montevideo',
	'America/Caracas',
	'Asia/Vladivostok',
	'Antarctica/Vostok',
	'Pacific/Efate',
	'Africa/Ndjamena',
	'Africa/El_Aaiun',
	'Africa/Brazzaville',
	'Africa/Bangui',
	'Africa/Douala',
	'Africa/Lagos',
	'Africa/Libreville',
	'Africa/Luanda',
	'Atlantic/Faeroe',
	'Atlantic/Faroe',
	'America/Godthab',
	'Australia/Perth',
	'Antarctica/Casey',
	'Australia/West',
	'Asia/Yakutsk',
	'UTC',
);