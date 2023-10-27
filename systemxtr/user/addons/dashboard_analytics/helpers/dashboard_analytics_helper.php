<?php

function daAveragePagesPerVisit($pageviews, $visits)
{
	return ($pageviews > 0 && $visits > 0) ? round(floatval($pageviews / $visits), 2) : 0;
}
	
function daAverageVisitLength($seconds, $visits)
{
	if($seconds > 0 && $visits > 0)
	{
		$avg_secs = $seconds / $visits;
		// This little snippet by Carson McDonald, from his Analytics Dashboard WP plugin
		$hours = floor($avg_secs / (60 * 60));
		$minutes = floor(($avg_secs - ($hours * 60 * 60)) / 60);
		$seconds = $avg_secs - ($minutes * 60) - ($hours * 60 * 60);
		return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
	}
	else
	{
		return '00:00:00';
	}
}

function daBounceRate($bounces, $sessions, $entrances)
{
	return ($bounces > 0 && $sessions > 0) ? round( ($bounces / $entrances) * 100, 2 ).'%' : '0%';
}

function daFlagIcon($country)
{
	$countries = array("AF" => "Afghanistan", "AX" => "Aland Islands", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua and Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BQ" => "Bonaire, Saint Eustatius and Saba", "BA" => "Bosnia & Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", "BN" => "Brunei Darussalam", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos (Keeling) Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo", "CD" => "Congo (DR)", "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Cote d'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CW" => "Curacao", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands (Malvinas)", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GG" => "Guernsey", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard and McDonald Islands", "VA" => "Holy See (Vatican City State)", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran", "IQ" => "Iraq", "IE" => "Ireland", "IM" => "Isle of Man", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JE" => "Jersey", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KP" => "North Korea", "KR" => "South Korea", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Laos", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macao", "MK" => "Macedonia", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestine", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RE" => "Reunion", "RO" => "Romania", "RU" => "Russia", "RW" => "Rwanda", "BL" => "Saint Barthelemy", "SH" => "Saint Helena", "KN" => "Saint Kitts and Nevis", "LC" => "Saint Lucia", "MF" => "Saint Martin", "PM" => "Saint Pierre and Miquelon", "VC" => "Saint Vincent and the Grenadines", "WS" => "Samoa", "SM" => "San Marino", "ST" => "Sao Tome and Principe", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SX" => "Sint Maarten", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia and South Sandwich Islands", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SS" => "South Sudan", "SR" => "Suriname", "SJ" => "Svalbard and Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syria", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad and Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks and Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UM" => "United States Minor Outlying Islands", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Vietnam", "VG" => "Virgin Islands (British)", "VI" => "Virgin Islands (U.S.)", "WF" => "Wallis and Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");
	
	if($code = array_search($country, $countries))
	{
		return '<span class="flag-icon flag-icon-'.strtolower($code).'"></span>';
	}
}

function daDeviceChartData($data)
{
	$stats = array(
		'cols' => array(
			array('label' => ucfirst(lang('da_device')), 'type' => 'string'),
			array('label' => ucfirst(lang('da_visits')), 'type' => 'number')
		),
		'rows' => array()
	);	
		
	foreach($data as $row)
	{
		$stats['rows'][] = array('c' => array(
			array('v' => strtoupper($row[0])),
			array('v' => floatval($row[1]))
		));
	}
	
	return $stats;
}
	
function daHourlyChartData($data, $metric)
{
	$stats = array(
		'cols' => array(
			array('id' => 'time', 'type' => 'string'),
			array('id' => $metric, 'type' => 'number')
		),
		'rows' => array()
	);
	
	foreach($data as $row)
	{
		switch($metric) {
			case "pageviews":
				$datapoint = $row[1];
				break;
			case "visits":	
				$datapoint = $row[2];
				break;
			case "avg_visit":
				$datapoint = $row[3];
				break;
			case "pages_per_visit":
				$datapoint = ($row[1] > 0 && $row[2] > 0) ? $row[1] / $row[2] : 0;
				break;
			case "bounce_rate":
				$datapoint = ($row[4] > 0 && $row[2] > 0) ? $row[4] / $row[5] : 0;
				break;
		}
		$stats['rows'][] = array('c' => array(
			array('v' => $row[0]),
			array('v' => floatval($datapoint))
		));
	}
	return $stats;
}

function daMonthlyChartData($data, $days = 30, $date_format = 'M j')
{
	if($days < count($data))
	{
		$data = array_slice($data, -$days, $days, true);
	}
	
	$stats = array(
		'cols' => array(
			array('label' => ucfirst(lang('da_date')), 'type' => 'string'),
			array('label' => ucfirst(lang('da_visits')), 'type' => 'number'),
			array('label' => ucfirst(lang('da_pageviews')), 'type' => 'number')
		),
		'rows' => array()
	);	
		
	foreach($data as $date => $row)
	{
		$stats['rows'][] = array('c' => array(
			array('v' => date($date_format, strtotime($row[0].' 12:00:00'))),
			array('v' => floatval($row[2])),
			array('v' => floatval($row[1]))
		));
	}
	return $stats;
}

function daPercentage($value, $total, $rounded = false)
{
	$v = ( $value / $total ) * 100;
	if($rounded == true)
	{
		return (number_format($v) < 1) ? 1 : number_format($v);
	}
	else
	{
		return $v;
	}
}

function daProcessLink($url, $title)
{
	return '<a href="'.QUERY_MARKER.'URL='.urlencode($url).'" target="_blank" title="'.$title.'">'.$title.'</a>';
}

function daSparklineUrl($rows, $metric)
{
	$max = 0; $stats = array();
	
	foreach($rows as $row)
	{
		switch($metric) {
			case "pageviews":
				$datapoint = $row[1];
				break;
			case "visits":	
				$datapoint = $row[2];
				break;
			case "avg_visit":
				$datapoint = $row[3];
				break;
			case "pages_per_visit":
				$datapoint = ($row[1] > 0 && $row[2] > 0) ? $row[1] / $row[2] : 0;
				break;
			case "bounce_rate":
				$datapoint = ($row[4] > 0 && $row[2] > 0) ? $row[4] / $row[5] : 0;
				break;
		}
		$max = ($max < $datapoint) ? $datapoint : $max;
		$stats[] = $datapoint;
	}
	
	// Build Google Chart url
	$base = 'https://chart.googleapis.com/chart?';
	$args = array(
		'cht=ls',
		'chs=480x80',
		'chls=2',
		'chm=B,1f80bd35,0,0,0',
		'chco=1f80bd',
		'chf=c,s,FFFFFF00|bg,s,FFFFFF00',
		'chd=t:'.implode(',', $stats),
		'chds=0,'.$max
	);
	
	return $base.implode('&amp;', $args);
}

function daUsersChartData($newSessions, $totalSessions)
{
	$stats = array(
		'cols' => array(
			array('label' => ucfirst(lang('da_type')), 'type' => 'string'),
			array('label' => ucfirst(lang('da_sessions')), 'type' => 'number')
		),
		'rows' => array(
			array('c' => array(
				array('v' => strtoupper(lang('da_new'))),
				array('v' => floatval($newSessions)),
				)
			),
			array('c' => array(
				array('v' => strtoupper(lang('da_returning'))),
				array('v' => floatval($totalSessions - $newSessions))
				)
			)
		)
	);	
	
	return $stats;
}