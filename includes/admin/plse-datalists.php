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

    /**
     * Set a language field accord to Schema requirements.
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $languages    language list.
     */
    private $languages = array(
        'en' => 'English',
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

    private $us_states = array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District Of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming'
    );

    /**
     * Set a countries field accord to Schema requirements.
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $countries    country list.
     */
    private $countries = array(
        'US' => 'United States',
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

    /**
     * Provide common defaults for game genres, for Game Schema, with 
     * URL to Wikipedia entry.
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $game_genres    game genre list.
     */
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

    /**
     * Provide common defaults for digital platforms for Game Schema.
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $platforms    platform list.
     */
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
        'playstation' => 'Playstation 3',
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
        'orbisOS' => 'PlayStation 4',
        'cellOS' => 'PlayStation 3',
        'playstation 2 linux' => 'PlayStation 2',
        'freebsd' => 'Free BSD (Nintendo Switch)'
    );

    /**
     * ISO currency codes https://en.wikipedia.org/wiki/ISO_4217
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $currency    currency list.
     */
    private $currency = array(
        'USD' => 'United States dollar',
        'CRYPTO' => 'Crypto',
        'AED' => 'United Arab Emirates dirham',
        'AFN' => 'Afghanistan afghani',
        'ALL' => 'Albanian lek',
        'AMD' => 'Armenian dram',
        'ANG' => 'Netherlands guilder',
        'AOA' => 'Angolan kwanza',
        'ARS' => 'Argentine peso',
        'AUD' => 'Australian dollar',
        'AWG' => 'Aruban florin',
        'AZN' => 'Azerbaijani manat',
        'BAM' => 'Bosnia and Herzegovina mark',
        'BBD' => 'Barbados dollar',
        'BDT' => 'Bangladeshi taka',
        'BGN' => 'Bulgarian lev',
        'BHD' => 'Bahraini dinar',
        'BIF' => 'Burundian franc',
        'BMD' => 'Bermudian dollar',
        'BND' => 'Brunei dollar',
        'BOB' => 'Bolivia Boliviano',
        'BRL' => 'Brazilian real',
        'BSD' => 'Bahamian dollar',
        'BTN' => 'Bhutanese ngultrum',
        'BWP' => 'Botswana pula',
        'BYN' => 'Belarusian ruble',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CDF' => 'Congolese franc',
        'CHF' => 'Swiss franc',
        'CLP' => 'Chilean peso',
        'CNY' => 'Chinese yuan',
        'COP' => 'Colombian peso',
        'CRC' => 'Costa Rican colon',
        'CUP' => 'Cuban peso ',
        'CVE' => 'Cape Verdean escudo',
        'CZK' => 'Czech koruna',
        'DJF' => 'Djiboutian franc',
        'DKK' => 'Danish krone',
        'DOP' => 'Dominican peso',
        'DZD' => 'Algerian dinar',
        'EGP' => 'Egyptian pound',
        'ERN' => 'Eritrean nakfa',
        'ETB' => 'Ethiopian birr',
        'EUR' => 'Euro',
        'FJD' => 'Fiji dollar',
        'FKP' => 'Falkland Islands pound',
        'GBP' => 'Pound sterling',
        'GEL' => 'Georgian lari',
        'GHS' => 'Ghanaian cedi',
        'GIP' => 'Gibraltar pound',
        'GMD' => 'Gambian dalasi',
        'GNF' => 'Guinean franc',
        'GTQ' => 'Guatemalan quetzal',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HNL' => 'Honduran lempira',
        'HRK' => 'Croatian kuna',
        'HTG' => 'Haitian gourde',
        'HUF' => 'Hungarian forint',
        'IDR' => 'Indonesian rupiah',
        'ILS' => 'Israeli new shekel',
        'INR' => 'Indian rupee',
        'IQD' => 'Iraqi dinar',
        'IRR' => 'Iranian rial',
        'ISK' => 'Icelandic króna',
        'JMD' => 'Jamaican dollar',
        'JOD' => 'Jordanian dinar',
        'JPY' => 'Japanese yen',
        'KES' => 'Kenyan shilling',
        'KGS' => 'Kyrgyzstani som',
        'KHR' => 'Cambodian riel',
        'KMF' => 'Comoro franc',
        'KPW' => 'North Korean',
        'KRW' => 'South Korean',
        'KWD' => 'Kuwaiti dinar',
        'KYD' => 'Cayman Islands dollar',
        'KZT' => 'Kazakhstani tenge',
        'LAK' => 'Lao kip',
        'LBP' => 'Lebanese pound',
        'LKR' => 'Sri Lankan rupee',
        'LRD' => 'Liberian dollar',
        'LSL' => 'Lesotho loti',
        'LYD' => 'Libyan dinar',
        'MAD' => 'Moroccan dirham',
        'MDL' => 'Moldovan leu',
        'MGA' => 'Malagasy ariary',
        'MKD' => 'Macedonian denar',
        'MMK' => 'Myanmar kyat',
        'MNT' => 'Mongolian tögrög',
        'MOP' => 'Macanese pataca',
        'MRU' => 'Mauritanian ouguiya',
        'MUR' => 'Mauritian rupee',
        'MVR' => 'Maldivian rufiyaa',
        'MWK' => 'Malawian kwacha',
        'MXN' => 'Mexican peso',
        'MYR' => 'Malaysian ringgit',
        'MZN' => 'Mozambican metical',
        'NAD' => 'Namibian dollar',
        'NGN' => 'Nigerian naira',
        'NIO' => 'Nicaraguan córdoba',
        'NOK' => 'Norwegian krone',
        'NPR' => 'Nepalese rupee',
        'NZD' => 'New Zealand dollar',
        'OMR' => 'Omani rial',
        'PAB' => 'Panamanian balboa',
        'PEN' => 'Peruvian sol',
        'PGK' => 'Papua New Guinean kina',
        'PHP' => 'Philippine peso',
        'PKR' => 'Pakistani rupee',
        'PLN' => 'Polish złoty',
        'PYG' => 'Paraguayan guaraní',
        'QAR' => 'Qatari riyal',
        'RON' => 'Romanian leu',
        'RSD' => 'Serbian dinar',
        'RUB' => 'Russian ruble',
        'RWF' => 'Rwandan franc',
        'SAR' => 'Saudi riyal',
        'SBD' => 'Solomon Islands dollar',
        'SCR' => 'Seychelles rupee',
        'SDG' => 'Sudanese pound',
        'SEK' => 'Swedish krona',
        'SGD' => 'Singapore dollar',
        'SHP' => 'Saint Helena pound',
        'SLL' => 'Sierra Leonean leone',
        'SOS' => 'Somali shilling',
        'SRD' => 'Surinamese dollar',
        'SSP' => 'South Sudanese pound',
        'STN' => 'São Tomé and Príncipe dobra',
        'SVC' => 'Salvadoran colón',
        'SYP' => 'Syrian pound',
        'SZL' => 'Swazi lilangeni',
        'THB' => 'Thai baht',
        'TJS' => 'Tajikistani somoni',
        'TMT' => 'Turkmenistan manat',
        'TND' => 'Tunisian dinar',
        'TOP' => 'Tongan paʻanga',
        'TRY' => 'Turkish lira',
        'TTD' => 'Trinidad and Tobago dollar',
        'TWD' => 'New Taiwan dollar',
        'TZS' => 'Tanzanian shilling',
        'UAH' => 'Ukrainian hryvnia',
        'UGX' => 'Ugandan shilling',
        'UYU' => 'Uruguayan peso',
        'UZS' => 'Uzbekistan som',
        'VES' => 'Venezuelan bolívar soberano',
        'VND' => 'Vietnamese đồng',
        'VUV' => 'Vanuatu vatu',
        'WST' => 'Samoan tala',
        'XAF' => 'CFA franc',
        'XCD' => 'East Caribbean dollar',
        'XOF' => 'CFA franc BCEAO',
        'XPF' => 'CFP franc (franc Pacifique)',
        'YER' => 'Yemeni rial',
        'ZAR' => 'South African rand',
        'ZMW' => 'Zambian kwacha',
        'ZWL' => 'Zimbabwean dollar',
    );

    /**
     * Based on Google's list for softwareApplication
     * {@link https://developers.google.com/search/docs/advanced/structured-data/software-app}
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $application_category    software application list.
     */
    private $application_category = array(
        'GameApplication' => 'Game',
        'MobileApplication' => 'Mobile App',
        'WebApplication' => 'Web App',
        'SocialNetworkingApplication' => 'Social Networking',
        'TravelApplication' => 'Travel',
        'ShoppingApplication' => 'Online Shopping',
        'SportsApplication' => 'Sports',
        'LifestyleApplication' => 'Lifestyle',
        'BusinessApplication' => 'Business',
        'DesignApplication' => 'Design Tool',
        'DeveloperApplication' => 'Developer Tool',
        'DriverApplication' => 'System Driver',
        'EducationalApplication' => 'Educational',
        'HealthApplication' => 'Healthcare',
        'FinanceApplication' => 'Finance',
        'SecurityApplication' => 'Security',
        'BrowserApplication' => 'Browser',
        'CommunicationApplication' => 'Communication',
        'DesktopEnhancementApplication' => 'Desktop Utility',
        'EntertainmentApplication' => 'Entertainment',
        'MultimediaApplication' => 'Multimedia',
        'HomeApplication' => 'Home',
        'UtilitiesApplication' => 'Utilities',
        'ReferenceApplication' => 'Reference',
    );

    /**
     * Actions to take, check Schema.org full hierarchy, used by 
     * Service schema.
     * {@link https://schema.org/docs/full.html}
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $actions    actions list.
     */
    private $actions = array(
        'AchieveAction' => 'Achieve (general)',
        'LoseAction' => 'Lose (game, bet)',
        'TieAction' => 'Tie (game, bet)',
        'WinAction' => 'Win (game, bet)',

        'AssessAction' => 'Assess (general)',
        'ChooseAction' => 'Choose',
        'IgnoreAction' => 'Ignore',
        'VoteAction' => 'Vote',
        'ReactAction' => 'React to Something',
        'AgreeAction' => 'Agree With',
        'DisagreeAction' => 'Disagree With',
        'DislikeAction' => 'Dislike Something',
        'EndorseAction' => 'Endorse Something',
        'LikeAction' => 'Like Something',
        'WantAction' => 'Want Something',

        'ConsumeAction' => 'Consume (general)',
        'WearAction' => 'Wear',
        'DrinkAction' => 'Drink',
        'EatAction' => 'Eat',
        'InstallAction' => 'Install',
        'ListenAction' => 'Listen',
        'ReadAction' => 'Read',
        'UseAction' => 'Use',
        'ViewAction' => 'View',
        'WatchAction' => 'Watch',

        'ControlAction' => 'Control (General)',
        'ActivateAction' => 'Activate',
        'DeactivateAction' => 'Deactivate',
        'ResumeAction' => 'Resume',
        'SuspendAction' => 'Suspend',

        'CreateAction' => 'Create',
        'CookAction' => 'Cook',
        'DrawAction' => 'Draw',
        'FilmAction' => 'Film',
        'PaintAction' => 'Paint',
        'PhotographAction' => 'Photograph',
        'WriteAction' => 'Write',

        'FindAction' => 'Find (not search)',
        'CheckAction' => 'Check',
        'DiscoverAction' => 'Discover',
        'TrackAction' => 'Track',

        'InteractAction' => 'Interact (general)',
        'BefriendAction' => 'Befriend',
        'CommunicateAction' => 'Communicate',
        'AskAction' => 'Ask',
        'CheckInAction' => 'Check In',
        'CheckOutAction' => 'Check Out',
        'CommentAction' => 'Comment',
        'InformAction' => 'Inform',
        'InviteAction' => 'Invite',
        'ReplyAction' => 'Reply',
        'ShareAction ' => 'Share',

        'FollowAction' => 'Follow',
        'JoinAction' => 'Join',
        'LeaveAction' => 'Leave',
        'MarryAction' => 'Marry',
        'RegisterAction' => 'Register',
        'SubscribeAction' => 'Subscribe',
        'UnRegisterAction' => 'Unregister',
    
        'MoveAction' => 'Move (general)',
        'ArriveAction' => 'Arrive',
        'DepartAction' => 'Depart',
        'TravelAction' => 'Travel',

        'OrganizeAction' => 'Organize (general)',
        'AllocateAction' => 'Allocate',
        'AcceptAction' => 'Accept',
        'AssignAction' => 'Assign',
        'AuthorizeAction' => 'Authorize',
        'RejectAction' => 'Reject',
        'ApplyAction' => 'Apply',
        'BookmarkAction' => 'Bookmark',
        'PlanAction' => 'Plan',
        'CancelAction' => 'Cancel',
        'ReserveAction' => 'Reserve',
        'ScheduleAction' => 'Schedule',

        'PlayAction' => 'Play',
        'ExerciseAction' => 'Exercise',
        'PerformAction ' => 'Perform',

        'SearchAction' => 'Search',
        'SeekToAction' => 'Seek',
        'SolveMathAction' => 'Solve',
        'TradeAction' => 'Trade',
        'BuyAction' => 'Buy',
        'DonateAction' => 'Donate',
        'OrderAction' => 'Order',
        'PayAction' => 'Pay',
        'PreOrderAction' => 'Pre-Order',
        'QuoteAction' => 'Quote (a price)',
        'RentAction' => 'Rent',
        'SellAction' => 'Sell',
        'TipAction' => 'Tip (for service)',
    
        'TransferAction' => 'Transfer (general)',
        'BorrowAction' => 'Borrow Something',
        'DownloadAction' => 'Download',
        'GiveAction' => 'Give Something',
        'LendAction' => 'Lend Something',
        'MoneyTransfer' => 'Transfer Money',
        'ReceiveAction' => 'Receive Something',
        'ReturnAction' => 'Return Item',
        'SendAction' => 'Send Something',
        'TakeAction' => 'Take Something',

        'UpdateAction' => 'Update (general)',
        'AddAction' => 'Add',
        'InsertAction' => 'Insert',
        'AppendAction' => 'Append',
        'PrependAction' => 'Prepend',
        'DeleteAction' => 'Delete',
        'ReplaceAction' => 'Replace'


    );

    /**
     * Creative Work types, from Schema.org
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $creative_works    Schema.org Creative Work list.
     */
    private $creative_works = array(
        'AmpStory' => 'AMP Mobile Page',
        'ArchiveComponent' => 'Data Archive',
        'Article' => 'Article',
        'Atlas' => 'Atlas',
        'Blog' => 'Blog',
        'Book' => 'Book',
        'Chapter' => 'Book Chapter',
        'Claim' => 'Fact Claimed',
        'Clip' => 'Segment of movie, TV, song',
        'Collection' => 'A collection of works',
        'ComicStory' => 'Part of Comic Book',
        'Comment' => 'Comment on something',
        'Conversation' => 'Conversation',
        'Course' => 'Course',
        'CreativeWorkSeason' => 'Episodes (one season)',
        'CreativeWorkSeries' => 'Episodes (complete)',
        'DataCatalog' => 'Data, multiple sets',
        'Dataset' => 'One set of data',
        'DefinedTermSet' => 'Dictionary, glossary',
        'Diet' => 'Diet (food)',
        'DigitalDocument' => 'Electronic file, document',
        'Drawing' => 'Drawing',
        'EducationalOccupationalCredential' => 'Education Degree, Credential',
        'Episode' => 'Episode (single)',
        'ExercisePlan' => 'Exercise Plan',
        'Game' => 'Game',
        'Guide' => 'Guide',
        'HowTo' => 'HowTo article',
        'HowToDirection' => 'HowTo (one direction)',
        'HowToSection' => 'HowTo (sub-section)',
        'HowToStep' => 'HowTo (one step)',
        'HowToTip' => 'HowTo (tip)',
        'HyperToc' => 'Content List (rich media)',
        'HyperTocEntry' => 'Content Item (rich media)',
        'LearningResource' => 'Learning Resource',
        'Legislation' => 'Legislation',
        'Manuscript' => 'Manuscript',
        'Map' => 'Map',
        'MathSolver' => 'Math Solver Page',
        'MediaObject' => 'Media Object',
        'MediaReviewItem' => 'Review of Media',
        'Menu' => 'Menu',
        'MenuSection' => 'Section of Menu',
        'Message' => 'Message',
        'Movie' => 'Movie',
        'MusicComposition' => 'Musical Composition',
        'MusicPlaylist' => 'Music Playlist',
        'MusicRecording' => 'Music Recording',
        'Painting' => 'Painting',
        'Photograph' => 'Photograph',
        'Play' => 'Play in Theater',
        'Poster' => 'Poster',
        'PublicationIssue' => 'Publication (one issue)',
        'PublicationVolume' => 'Publication (entire volume)',
        'Quotation' => 'Quote',
        'Review' => 'Review',
        'Sculpture' => 'Sculpture',
        'SheetMusic' => 'Sheet Music',
        'ShortStory' => 'Short Story',
        'SoftwareApplication' => 'Software Application',
        'SoftwareSourceCode' => 'Source Code',
        'SpecialAnnouncement' => 'Special Announcement',
        'Statement' => 'Interesting Fact',
        'TVSeason' => 'Televsion Season',
        'TVSeries' => 'Television Series',
        'Thesis' => 'Thesis',
        'VisualArtwork' => 'Visual Art',
        'WebContent' => 'Web Content',
        'WebPage' => 'Web Page',
        'WebPageElement' => 'Web Page Element',
        'WebSite' => 'Web Site',
    );

    /**
     * Event types, based on Schema.org list
     * {@link https://schema.org/Event}
     * 
     * @since    1.0.0
     * @access   private
     * @var      array    $event_types    Schema.org Event type list.
     */
    private $event_types = array(
        'Event' => 'Event', // super-type
        'BusinessEvent' => 'Business',
        'ChildrensEvent' => 'Childrens',
        'ComedyEvent' => 'Comedy',
        'CourseInstance' => 'Course',
        'DanceEvent' => 'Dance',
        'DeliveryEvent' => 'Delivery',
        'EducationEvent' => 'Education',
        'EventSeries' => 'Event Series',
        'ExhibitionEvent' => 'Exhibition',
        'Festival' => 'Festival',
        'FoodEvent' => 'Food',
        'Hackathon' => 'Hackathon',
        'LiteraryEvent' => 'Literary',
        'MusicEvent' => 'Music',
        'PublicationEvent' => 'Publication',
        'SaleEvent' => 'Sale',
        'ScreeningEvent' => 'Screening',
        'SocialEvent' => 'Social',
        'SportsEvent' => 'Sports',
        'TheaterEvent' => 'Theater',
        'VisualArtsEvent' => 'Visual Arts'
    );

    private $offer_types = array(
        'https://schema.org/InStock' => 'In Stock',
        'https://schema.org/SoldOut' => 'Sold Out',
        'https://schema.org/PreOrder' => 'Pre-Order',
    );

    /*
     * Google doesn't list any Service types 
     * Schema.org lists a few {@link https://schema.org/serviceType} 
     * Others created using similar syntax from common lists of business Services
     */
    private $service_genres = array(
        'KnowledgeService' => 'Knowledge',
        'DesignService' => 'Design',
        'GraphicDesignService' => 'Graphic Design',
        'UserExperienceService' => 'User Experience (UX)',
        'SoftwareService' => 'Software and Coding',
        'DataProcessingService' => 'Data Processing',
        'InformationTechnologyService' => 'IT (Information Technology)',
        'TranslationService' => 'Translation',
        'HumanResourcesService' => 'HR (Human Resources)',
        'ConsultingService' => 'Consulting',
        'ManagementService' => 'Management',
        'ProjectManagementService' => 'Project Management',
        'MarketingService' => 'Marketing',
        'PublicRelationsService' => 'Public Relations',
        'BankingService' => 'Banking',
        'SalesService' => 'Sales',
        'ProductionService' => 'Production',
        'RetailService' => 'Retail',
        'DistibutionService' => 'Distribution',
        'StreamingService' => 'Streaming Services',
        'SocialMediaService' => 'Social Media',
        'SupplyChainService' => 'Supply Chain',
        'PackagingService' => 'Packaging',
        'EntertainmentService' => 'Entertainment',
        'MusicService' => 'Musician',
        'ArtService' => 'Artist',
        'WebDesignService' => 'Web Designer',
        'WebDevelopmentService' => 'Web Developer',
        'GameDesignService' => 'Game Design',
        'GameDevelopmentService' => 'Game Development',
        'CreativeService' => 'Creative Services',
        'EducationService' => 'Education',
        'ConstructionService' => 'Construction',
        'MaintenanceService' => 'Maintenance',
        'CleaningService' => 'Cleaning',
        'WasteManagementService' => 'Waste Management',
        'RealEstateService' => 'Real Estate',
        'ProfessionalService' => 'Professional Services',
        'SecurityService' => 'Security',
        'HealthcareService' => 'Healthcare',
        'WellnessGroomingService' => 'Wellness and Grooming',
        'PetService' => 'Pets',
        'FitnessService' => 'Sports and Fitness',
        'HospitalityService' => 'Hospitality',
        'TransportService' => 'Transport',
        'UtilityService' => 'Utilities',
        'InsuranceService' => 'Insurance',
        'RentalService' => 'Rentals',
        'EventService' => 'Events',

        // defined in schema.org
        'BroadcastService' => 'Broadcast',
        'CableOrSatelliteService' => 'Cable/Satellite',
        'FinancialProduct' => 'Financial Product',
        'FoodService' => 'Food Service',
        'GovernmentService' => 'Government',
        'TaxiService' => 'Taxi',
        'WebAPI' => 'Web API'

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
     * @return   PLSE_Datalists    $self__instance
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

    /**
     * Reverse an array.
     * 
     * @since    1.0.0
     * @access   public
     * @param    array|string    either an array, or slug to find an array in this class
     * @return   array|null      array with keys and values reversed
     */
    public function get_rev_arr ( $arr ) {

        if ( isset ( $arr ) ) {

            if ( is_array( $arr ) ) {
                
                return array_flip( $arr );

            } else if ( is_string( $arr ) ) {

                return array_flip( $this->{ $arr } );

            }

        }
        return null;

    }

     /**
      * Datalist attaches a list to an <input type="text"... field.
      * 
      * @since    1.0.0
      * @access   public
      * @param    array     $arr    an associative array stored $value => $key
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
      * @param    array|string    $arr       either the name of a standard $value => $key array in PLSE_Datalists, or a custom array $key => $value array to use
      * @param    array|string    $selected  the value(s) in the DB, single or array
      * @param    boolean         $reverse   if true, reverse keys and values (need to use datalist arrays in <select>)
      * @return   string          $list      the <option... list (not the complete <select> control)
      */
     public function get_select ( $arr, $selected = '', $reverse = false ) {

        // if string is passed for array options, use PHP $$ 'variable variable' to convert string to local array name
        if ( ! is_array( $arr ) ) {
            $arr = $this->{$arr}; // $arr is the name of a standard array in this class
            if ( ! is_array( $arr ) ) {
                echo "ERROR - supplied datalist string does not correspond to array in PLSE_Datalists";
                return null;
            }

        }

        $list = '';

        // loop through options, assigning selected values
        foreach ( $arr as $key => $value ) {

            $sel_value = '';

            // if we are using a datalist (rather than supplied array), swap the variables
            if ( $reverse === true ) {
                $tmp = $key;
                $key = $value;
                $value = $tmp;
            }

            // determine if $selected is one value, or an array
            if ( is_array( $selected ) ) { 

                // multi select, array of selected options in DB
                foreach( $selected as $sel ) {
                    if ( $value == $sel) {
                        $sel_value = 'selected';
                    }
                }

            } else if ( $value == $selected ) { 

                // one string matching one of the options in DB
                $sel_value = 'selected';

            }

            $list .= '<option value="' . $value . '" ' . $sel_value . '>' . $key . '</option>';

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

    public function get_languages_select ( $value = '' ) {
        return $this->get_select( $this->languages, $value, true );
    }

    public function get_languages_size () {
        return count( $this->languages );
    }

    /**
     * Provide a list of USA state fields. Users type in 
     * values to progressively select the correct value
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_us_states_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-us-states-data';
        return $this->get_datalist( $this->us_states, $id );
    }

    public function get_us_states_select ( $value = '' ) {
        return $this->get_select( $this->us_states, $value, true );
    }

    public function get_us_states_size () {
        return count( $this->us_states );
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

    public function get_countries_select ( $value = '' ) {
        return $this->get_select( $this->countries, $value, true );
    }

    public function get_countries_size () {
        return count( $this->countries );
    }

    /**
     * Currency Codes
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_currency_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-currency-data';
        return $this->get_datalist( $this->currency, $id );
    }

    public function get_currency_select ( $value = '' ) {
        return $this->get_select( $this->currency, $value, true );
    }

    public function get_currency_size () {
        return count( $this->currency );
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

    public function get_game_genres_select ( $value = '' ) {
        return $this->get_select( $this->game_genres, $value, true );
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

    public function get_platforms_select ( $value = '' ) {
        return $this->get_select( $this->platforms, $value, true );
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

    public function get_os_select ( $value = '' ) {
        return $this->get_select( $this->os, $value, true );
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

    public function get_application_category_select ( $value = '' ) {
        return $this->get_select( $this->application_category, $value, true );
    }

    public function get_application_category_size () {
        return count( $this->application_category );
    }

    /**
     * Action Schema list
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_actions_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-actions-data';
        return $this->get_datalist( $this->actions, $id );
    }

    public function get_actions_select ( $value = '' ) {
        return $this->get_select( $this->actions, $value, true );
    }

    public function get_actions_size () {
        return count( $this->actions );
    }

    /**
     * CreativeWorks
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_creative_works_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-creative-works-data';
        return $this->get_datalist( $this->creative_works, $id );
    }

    public function get_creative_works_select ( $value = '' ) {
        return $this->get_select( $this->creative_works, $value, true );
    }

    public function get_creative_works_size () {
        return count( $this->creative_works );
    }

    /**
     * Provide a list of event types
     * 
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_event_types_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse-event-types-data';
        return $this->get_datalist( $this->event_types, $id );
    }

    public function get_event_types_select ( $value = '' ) {
        return $this->get_select( $this->event_types, $value, true );
    }

    public function get_event_types_size () {
        return count( $this->event_types );
    }

    /**
     * Provide a list of Offer types
     * @since    1.0.0
     * @access   public
     * @return   string    HTML for a <datalist>
     */
    public function get_offer_types_datalist ( $id = '' ) {
        if ( ! $id ) $id = 'plse_offer_types-data';
        return $this->get_datalist( $this->offer_types, $id );
    }

    public function get_offer_types_select ( $value = '' ) {
        return $this->get_select( $this->offer_types, $value, true );
    }

    public function get_offer_types_size () {
        return count( $this->offer_types );
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

    public function get_service_genres_select ( $value = '' ) {
        return $this->get_select( $this->service_genres, $value, true );
    }

    public function get_service_genres_size () {
        return count( $this->service_genres );
    }

} // end of class