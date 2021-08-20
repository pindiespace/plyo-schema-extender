<?php

/**
 * Initializes plugin features, creates default plugin menu and options page.
 *
 * @since      1.0.0
 * @category   WordPress_Plugin
 * @package    PLSE_SCHEMA_Extender
 * @subpackage PlyoSchema_Extender/admin
 * @author     Pete Markeiwicz <pindiespace@gmail.com>
 * @license    GPL-2.0+
 * @link       https://plyojump.com
 */
class PLSE_Datalists {

    /**
     * Store reference for singleton pattern.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $instance    static reference to initialized class.
     */
    static private $__instance = null;

    private $languages = array(
        'af' => 'Afrikaans',
        'sq' => 'Albanian - shqip',
        'am' => 'Amharic - አማርኛ',
        'ar' => 'Arabic - العربية',
        'an' => 'Aragonese - aragonés',
        'hy' => 'Armenian - հայերեն',
        'ast' => 'Asturian - asturianu',
        'az' => 'Azerbaijani - azərbaycan dili',
        'eu' => 'Basque - euskara',
        'be' => 'Belarusian - беларуская',
        'bn' => 'Bengali - বাংলা',
        'bs' => 'Bosnian - bosanski',
        'br' => 'Breton - brezhoneg',
        'bg' => 'Bulgarian - български',
        'ca' => 'Catalan - català',
        'ckb' => 'Central Kurdish - کوردی (دەستنوسی عەرەبی)',
        'zh' => 'Chinese - 中文',
        'zh-HK' => 'Chinese (Hong Kong) - 中文（香港）',
        'zh-CN' => 'Chinese (Simplified) - 中文（简体）',
        'zh-TW' => 'Chinese (Traditional) - 中文（繁體）',
        'co' => 'Corsican',
        'hr' => 'Croatian - hrvatski',
        'cs' => 'Czech - čeština',
        'da' => 'Danish - dansk',
        'nl' => 'Dutch - Nederlands',
        'en' => 'English',
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'en-IN' => 'English (India)',
        'en-NZ' => 'English (New Zealand)',
        'en-ZA' => 'English (South Africa)',
        'en-GB' => 'English (United Kingdom)',
        'en-US' => 'English (United States)',
        'eo' => 'Esperanto - esperanto',
        'et' => 'Estonian - eesti',
        'fo' => 'Faroese - føroyskt',
        'fil' => 'Filipino',
        'fi' => 'Finnish - suomi',
        'fr' => 'French - français',
        'fr-CA' => 'French (Canada) - français (Canada)',
        'fr-FR' => 'French (France) - français (France)',
        'fr-CH' => 'French (Switzerland) - français (Suisse)',
        'gl' => 'Galician - galego',
        'ka' => 'Georgian - ქართული',
        'de' => 'German - Deutsch',
        'de-AT' => 'German (Austria) - Deutsch (Österreich)',
        'de-DE' => 'German (Germany) - Deutsch (Deutschland)',
        'de-LI' => 'German (Liechtenstein) - Deutsch (Liechtenstein)',
        'de-CH' => 'German (Switzerland) - Deutsch (Schweiz)',
        'el' => 'Greek - Ελληνικά',
        'gn' => 'Guarani',
        'gu' => 'Gujarati - ગુજરાતી',
        'ha' => 'Hausa',
        'haw' => 'Hawaiian - ʻŌlelo Hawaiʻi',
        'he' => 'Hebrew - עברית',
        'hi' => 'Hindi - हिन्दी',
        'hu' => 'Hungarian - magyar',
        'is' => 'Icelandic - íslenska',
        'id' => 'Indonesian - Indonesia',
        'ia' => 'Interlingua',
        'ga' => 'Irish - Gaeilge',
        'it' => 'Italian - italiano',
        'it-IT' => 'Italian (Italy) - italiano (Italia)',
        'it-CH' => 'Italian (Switzerland) - italiano (Svizzera)',
        'ja' => 'Japanese - 日本語',
        'kn' => 'Kannada - ಕನ್ನಡ',
        'kk' => 'Kazakh - қазақ тілі',
        'km' => 'Khmer - ខ្មែរ',
        'ko' => 'Korean - 한국어',
        'ku' => 'Kurdish - Kurdî',
        'ky' => 'Kyrgyz - кыргызча',
        'lo' => 'Lao - ລາວ',
        'la' => 'Latin',
        'lv' => 'Latvian - latviešu',
        'ln' => 'Lingala - lingála',
        'lt' => 'Lithuanian - lietuvių',
        'mk' => 'Macedonian - македонски',
        'ms' => 'Malay - Bahasa Melayu',
        'ml' => 'Malayalam - മലയാളം',
        'mt' => 'Maltese - Malti',
        'mr' => 'Marathi - मराठी',
        'mn' => 'Mongolian - монгол',
        'ne' => 'Nepali - नेपाली',
        'no' => 'Norwegian - norsk',
        'nb' => 'Norwegian Bokmål - norsk bokmål',
        'nn' => 'Norwegian Nynorsk - nynorsk',
        'oc' => 'Occitan',
        'or' => 'Oriya - ଓଡ଼ିଆ',
        'om' => 'Oromo - Oromoo',
        'ps' => 'Pashto - پښتو',
        'fa' => 'Persian - فارسی',
        'pl' => 'Polish - polski',
        'pt' => 'Portuguese - português',
        'pt-BR' => 'Portuguese (Brazil) - português (Brasil)',
        'pt-PT' => 'Portuguese (Portugal) - português (Portugal)',
        'pa' => 'Punjabi - ਪੰਜਾਬੀ',
        'qu' => 'Quechua',
        'ro' => 'Romanian - română',
        'mo' => 'Romanian (Moldova) - română (Moldova)',
        'rm' => 'Romansh - rumantsch',
        'ru' => 'Russian - русский',
        'gd' => 'Scottish Gaelic',
        'sr' => 'Serbian - српски',
        'sh' => 'Serbo-Croatian - Srpskohrvatski',
        'sn' => 'Shona - chiShona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala - සිංහල',
        'sk' => 'Slovak - slovenčina',
        'sl' => 'Slovenian - slovenščina',
        'so' => 'Somali - Soomaali',
        'st' => 'Southern Sotho',
        'es' => 'Spanish - español',
        'es-AR' => 'Spanish (Argentina) - español (Argentina)',
        'es-419' => 'Spanish (Latin America) - español (Latinoamérica)',
        'es-MX' => 'Spanish (Mexico) - español (México)',
        'es-ES' => 'Spanish (Spain) - español (España)',
        'es-US' => 'Spanish (United States) - español (Estados Unidos)',
        'su' => 'Sundanese',
        'sw' => 'Swahili - Kiswahili',
        'sv' => 'Swedish - svenska',
        'tg' => 'Tajik - тоҷикӣ',
        'ta' => 'Tamil - தமிழ்',
        'tt' => 'Tatar',
        'te' => 'Telugu - తెలుగు',
        'th' => 'Thai - ไทย',
        'ti' => 'Tigrinya - ትግርኛ',
        'to' => 'Tongan - lea fakatonga',
        'tr' => 'Turkish - Türkçe',
        'tk' => 'Turkmen',
        'tw' => 'Twi',
        'uk' => 'Ukrainian - українська',
        'ur' => 'Urdu - اردو',
        'ug' => 'Uyghur',
        'uz' => 'Uzbek - o‘zbek',
        'vi' => 'Vietnamese - Tiếng Việt',
        'wa' => 'Walloon - wa',
        'cy' => 'Welsh - Cymraeg',
        'fy' => 'Western Frisian',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba - Èdè Yorùbá',
        'zu' => 'Zulu - isiZulu'
    );

    private $countries = array(
        'AF' => 'Afghanistan',
        'AX' => 'Aland Islands',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
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
        'BQ' => 'Bonaire, Sint Eustatius and Saba',
        'BA' => 'Bosnia and Herzegovina',
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
        'CD' => 'Congo, the Democratic Republic of the',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'CI' => 'Cote D\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curacao',
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
        'HM' => 'Heard Island and Mcdonald Islands',
        'VA' => 'Holy See (Vatican City State)',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran, Islamic Republic of',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, Democratic People\'s Republic of',
        'KR' => 'Korea, Republic of',
        'XK' => 'Kosovo',
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
        'MK' => 'Macedonia, the Former Yugoslav Republic of',
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
        'FM' => 'Micronesia, Federated States of',
        'MD' => 'Moldova, Republic of',
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
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'CS' => 'Serbia and Montenegro',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan, Province of China',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania, United Republic of',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'UM' => 'United States Minor Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands, British',
        'VI' => 'Virgin Islands, U.s.',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe'
    );

    // TODO: REVERSE LABEL AND VALUE
    private $game_genres = array(
        'https://en.wikipedia.org/wiki/Adventure_game' => 'Adventure',
        'https://en.wikipedia.org/wiki/Action_game' => 'Action',
        'https://en.wikipedia.org/wiki/Platform_game' => 'Platform',
        'https://en.wikipedia.org/wiki/Shooter_game' => 'Shooter',
        'https://en.wikipedia.org/wiki/Puzzle_video_game' => 'Puzzle',
        'https://en.wikipedia.org/wiki/First-person_shooter' => 'First-Person Shooter (FPS)',
        'https://en.wikipedia.org/wiki/Fighting_game' => 'Fighting Game',
        'https://en.wikipedia.org/wiki/Stealth_game' => 'Stealth', 
        'https://en.wikipedia.org/wiki/Beat_%27em_up' => 'Beat \'Em Up Game',
        'https://en.wikipedia.org/wiki/Survival_game' => 'Survival',
        'https://en.wikipedia.org/wiki/Rhythm_game' => 'Rhythm and Dance',
        'https://en.wikipedia.org/wiki/Battle_royale_game' => 'Battle Royale',
        'https://en.wikipedia.org/wiki/Interactive_film' => 'Interactive Movie',
        'https://en.wikipedia.org/wiki/Visual_novel' => 'Interactive Novel',
        'https://en.wikipedia.org/wiki/Role-playing_video_game' => 'Role-Playing (RPG)',
        'https://en.wikipedia.org/wiki/Action_role-playing_game' => 'Action RPG',
        'https://en.wikipedia.org/wiki/Massively_multiplayer_online_role-playing_game' => 'MMORPG',
        'https://en.wikipedia.org/wiki/Tactical_role-playing_game' => 'Tactical Strategy RPG',
        'https://en.wikipedia.org/wiki/Open_world' => 'Open World RPG',
        'https://en.wikipedia.org/wiki/List_of_Pok%C3%A9mon_video_games' => 'Pokemon-style Monster Trainer',
        'https://en.wikipedia.org/wiki/Gamification' => 'Gamified Software Application',
        'https://en.wikipedia.org/wiki/Construction_and_management_simulation' => 'Management Simulation',
        'https://en.wikipedia.org/wiki/Vehicle_simulation_game' => 'Flight or Vehicle Simulation',
        'https://en.wikipedia.org/wiki/Life_simulation_game' => 'Simulation Game',
        'https://en.wikipedia.org/wiki/List_of_artificial_pet_games' => 'Virtual Artificial Pet',
        'https://en.wikipedia.org/wiki/Real-time_strategy' => 'Real-Time Strategy',
        'https://en.wikipedia.org/wiki/4X' => '4X RPG',
        'https://en.wikipedia.org/wiki/Artillery_game' => 'Artillery Game',
        'https://en.wikipedia.org/wiki/Multiplayer_online_battle_arena' => 'Multiplayer Online Battle Arena',
        'https://en.wikipedia.org/wiki/Tower_defense' => 'Tower Defense',
        'https://en.wikipedia.org/wiki/Computer_wargame' => 'Wargame',
        'https://en.wikipedia.org/wiki/Sim_racing' => 'Racing',
        'https://en.wikipedia.org/wiki/Sports_video_game' => 'Virtual Sports',
        'https://en.wikipedia.org/wiki/Category:Casino_video_games' => 'Casino Game',
        'https://en.wikipedia.org/wiki/Casual_game' => 'Casual Game',
        'https://en.wikipedia.org/wiki/Trivia' => 'Trivia Game',
        'https://en.wikipedia.org/wiki/Party_game' => 'Party Game',
        'https://en.wikipedia.org/wiki/Advergame' => 'Advergame',
        'https://en.wikipedia.org/wiki/Educational_game' => 'Educational game',
        'https://en.wikipedia.org/wiki/Exergaming' => 'Exergame',
        'https://en.wikipedia.org/wiki/Board_game' => 'Board Game',
        'https://en.wikipedia.org/wiki/Serious_game' => 'Serious Game',
        'https://en.wikipedia.org/wiki/Esports' => 'Esports',
        'https://en.wikipedia.org/wiki/Arcade_game' => 'Arcade Game',
        'https://en.wikipedia.org/wiki/Sandbox_game' => 'Sandbox',
    );

    private $platforms = array(
        'android' => 'Android',
        'ios' => 'iOS',
        'windows desktop' => 'Windows Desktop',
        'mac desktop' => 'Mac Desktop',
        'apple TV' => 'Apple TV',
        'web' => 'Web Browser',
        'xbox' => 'XBox',
        'xbox360' => 'XBox 360',
        'xbox one' => 'XBox One',
        'playstation' => 'PlayStation',
        'plastation' => 'Playstation 3',
        'playstation 4' => 'Playstation 4',
        'playstation 5' => 'Playstation 5',
        'nintendo wii' => 'Nintendo Wii',
        'nintendo switch' => 'Nintendo Switch',
    );

    private $os = array(
        'android' => 'Android',
        'ios' => 'iOS',
        'steamos' => 'SteamOS',
        'macos' => 'MacOS',
        'windows' => 'Windows',
        'windows 10' => 'Windows 10',
        'windows 8' => 'Windows 8',
        'linux' => 'Linux',
        'freebsd' => 'Free BSD (Nintendo Switch)'
    );

    /**
     * Based on Google's list for softwareApplication
     * {@link https://developers.google.com/search/docs/advanced/structured-data/software-app}
     */
    private $application_category = array(
        'GameApplication' => 'GameApplication',
        'MobileApplication' => 'MobileApplication',
        'WebApplication' => 'WebApplication',
        'SocialNetworkingApplication' => 'SocialNetworkingApplication',
        'TravelApplication' => 'TavelApplication',
        'ShoppingApplication' => 'ShoppingApplication',
        'SportsApplication' => 'SportsApplication',
        'LifestyleApplication' => 'LifestyleApplication',
        'BusinessApplication' => 'BusinessApplication',
        'DesignApplication' => 'DesignApplication',
        'DeveloperApplication' => 'DeveloperApplication',
        'DriverApplication' => 'DriverApplication',
        'EducationalApplication' => 'EducationalApplication',
        'HealthApplication' => 'HealthApplication',
        'FinanceApplication' => 'FinanceApplication',
        'SecurityApplication' => 'SecurityApplication',
        'BrowserApplication' => 'BrowserApplication',
        'CommunicationApplication' => 'CommunicationApplication',
        'DesktopEnhancementApplication' => 'DesktopEnhancementApplication',
        'EntertainmentApplication' => 'EntertainmentApplication',
        'MultimediaApplication' => 'MultimediaApplication',
        'HomeApplication' => 'HomeApplication',
        'UtilitiesApplication' => 'UtilitiesApplication',
        'ReferenceApplication' => 'ReferenceApplication',
    );

    private $service_genres = array(
        'knowledge' => 'Knowledge',
        'design' => 'Design',
        'graphic design' => 'Graphic Design',
        'ux' => 'User Experience (UX)',
        'game design' => 'Game Design',
        'software and coding' => 'Software and Coding',
        'data processing' => 'Data Processing',
        'information technology' => 'IT (Information Technology)',
        'translation' => 'Translation',
        'human resources' => 'HR (Human Resources)',
        'consulting' => 'Consulting',
        'management' => 'Management',
        'project management' => 'Project Management',
        'marketing' => 'Marketing',
        'public relations' => 'Public Relations',
        'banking' => 'Banking',
        'sales' => 'Sales',
        'retail' => 'Retail',
        'distribution' => 'distribution',
        'streaming' => 'streaming services',
        'social media' => 'Social Media',
        'supply chain' => 'Supply Chain',
        'packaging' => 'Packaging',
        'entertainment' => 'Entertainment',
        'musician' => 'Musician',
        'artist' => 'Artist',
        'web design' => 'Web Designer',
        'web development' => 'Web Developer',
        'game design' => 'Game Design',
        'game development' => 'Game Development',
        'creative services' => 'Creative Services',
        'government' => 'Government',
        'education' => 'Education',
        'construction' => 'Construction',
        'maintenance' => 'Maintenance',
        'cleaning' => 'Cleaning',
        'waste management' => 'Waste Management',
        'real estate' => 'Real Estate',
        'professional' => 'Professional Services',
        'security' => 'Security',
        'healthcare' => 'Healthcare',
        'wellness and grooming' => 'Wellness and Grooming',
        'pets' => 'Pets',
        'sports and fitness' => 'Sports and Fitness',
        'hospitality' => 'Hospitality',
        'transport' => 'Transport',
        'utilities' => 'Utilities',
        'government' => 'Government',
        'insurance' => 'Insurance',
        'financial' => 'Financial',
        'food' => 'Food',
        'rentals' => 'Rentals',
        'events' => 'Events'

    );

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct () {

        // utilities
        $this->init = PLSE_Init::getInstance();

    }

    /**
     * Enable the singleton pattern.
     * @since    1.0.0
     * @access   public
     * @return   PLSE_Metabox_Datalists    $self__instance
     */
    public static function getInstance () {
        if ( is_null( self::$__instance ) ) {
            self::$__instance = new PLSE_Datalists();
        }
        return self::$__instance;
    }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - GENERAL
     * ---------------------------------------------------------------------
     */

    /**
     * Get an array by name.
     * 
     * @since    1.0.0
     * @access   public
     * @return   array
     */
    public function get_arr ( $array_slug ) {
        if ( isset( $this->{ $array_slug } ) ) {
            return $this->{$array_slug};
        }
        return null;
    }

    public function get_rev_arr ( $array_slug ) {
        if ( isset( $this->{ $array_slug } ) ) {
            $arr = array();
            foreach ( $this->{ $array_slug } as $key => $val ) {
                $arr[$val] = $key;
            }
            return $arr;
        }
        return null;
    }

     /**
      * Datalist attaches a list to an <input type="text"... field.
      * 
      * @since    1.0.0
      * @access   public
      * @param    array     $arr    an associative array
      * @param    string    $id     id to associated with text field
      * @return   string    $list   the complete datalist element
      */
     public function get_datalist ( $arr, $id ) {
        $list = '<datalist id="' . $id . '">';
        foreach ( $arr as $key => $value ) {
            $list .= '<option value="' . $value . '">';
        }
        $list .= '</datalist>';
        return $list;
     }

     /**
      * Select creates either a poppup menu (single select) or scrolling list (multi select).
      * 
      * @since    1.0.0
      * @access   public
      * @param    array|string    $arr    either the name of a standard array, or a custom array to use
      * @param    array|string    $selected    either the value that is selected, or an array of selected values
      * @return   string    $list the <option... list (not the complete <select> control)
      */
     public function get_select ( $arr, $selected = '' ) {

        // if string is passed for array options, use PHP $$ 'variable variable' to convert string to local array name
        if ( ! is_array( $arr ) ) { 
            $arr = $this->{$arr}; // $arr is the name of standard array
        }

        // loop through options, assigning selected values
        foreach ( $arr as $key => $value ) {
            $sel_value = '';
            if ( is_array( $selected) ) { // multi select, array of selected options
                foreach( $selected as $sel ) {
                    if ( $key == $sel) {
                        $sel_value = 'selected';
                    }
                }
            } else if ( $key == $selected ) { // single select, one string matching one of the options
                $sel_value = 'selected';
            }
            $list .= '<option value="' . $key . '" ' . $sel_value . '>' . $value . '</option>';
        }

        return $list;
     }

    /**
     * ---------------------------------------------------------------------
     * GETTERS - SPECIFIC
     * Plse_Metabox generates the method names from the $option_list 
     * if it is a string.
     * ---------------------------------------------------------------------
     */

    /**
     * Provide a list of common languages for Schema. Note that we 
     * do NOT use a two-letter code
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_languages_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-languages-data';
        return $this->get_datalist( $this->languages, $id );
    }

    public function get_languages_size () {
        return count( $this->languages );
    }

    /**
     * Provide a list of countries country fields. Users type in 
     * values to progressively select the correct value
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_countries_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-countries-data';
        return $this->get_datalist( $this->countries, $id );
    }

    public function get_countries_select () {
        return $this->get_select( $this->countries );
    }

    public function get_countries_size () {
        return count( $this->countries );
    }

    /**
     * Provide a list of common videogame and related game genres.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_game_genres_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-game_genres-data';
        return $this->get_datalist( $this->game_genres, $id );
    }

    public function get_game_genres_select () {
        return $this->get_select( $this->game_genres );
    }

    public function get_game_genres_size () {
        return count( $this->game_genres );
    }

    /**
     * Provide a list of common videogame platforms (hardware/software).
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_platforms_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-platforms-data';
        return $this->get_datalist( $this->platforms, $id );
    }

    public function get_platforms_select () {
        return $this->get_select( $this->platforms );
    }

    public function get_platforms_size () {
        return count( $this->platforms );
    }

    /**
     * Provide a list of common videogame operating systems.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_os_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-os-data';
        return $this->get_datalist( $this->os, $id );
    }

    public function get_os_select () {
        return $this->get_select( $this->os );
    }

    public function get_os_size () {
        return count( $this->os );
    }

    /**
     * Provide a list of SoftwareApplication types for Google
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_application_category_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-software-application-data';
        return $this->get_datalist( $this->application_category, $id );
    }

    public function get_application_category_select () {
        return $this->get_select( $this->application_category );
    }

    public function get_application_category_size () {
        return count( $this->application_category );
    }

    /**
     * Provide a list of common business service models.
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_service_genres_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-service_genres-data';
        return $this->get_datalist( $this->service_genres, $id );
    }

    public function get_service_genres_select () {
        return $this->get_select( $this->service_genres );
    }

    public function get_service_genres_size () {
        return count( $this->service_genres );
    }

} // end of class