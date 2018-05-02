<?php
//-------------------
// SAR info parser
// The output is an array filled with usage data (and some empty keys):
//-------------------

function get_sar_info($atr = 'cpu') {

	switch ($atr) {
		case 'cpu':
			$atr = '-u';
			break;
		case 'memory':
			$atr = '-r';
			break;
		case 'network':
			$atr = '-n DEV';
			break;
	}

	$data = shell_exec('LC_TIME=en_UK.utf8 sar ' . $atr); //execute 'sar' utility with UK locale parameter so the time format is 24H
	$data = explode("\n", $data); // divide output by lines and refill the array
	$data = array_slice($data, 3, -2); // offset the array so we skip header and footer	

	$out = array(); // declare the output array

	// Loop throw array and remove all spaces possible. Only data is allowed
	foreach ($data as $key => $item)
	{
		$data[$key] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $data[$key]);
		$data[$key] = rtrim($data[$key]);
		$data[$key] = explode(" ", $data[$key]);
	}
	
	// Loop throw array and filter non-data values. First level
	foreach ($data as $key_row => $row_array)
	{
		// Loop throw each key in a row. Second level
		foreach ($row_array as $key => $var)
		{
			// Filter non-data values like labels, info strings and force them to 0
			if (!preg_match('/All|Average|LINUX|CPU|%/i',$var)) {
				$row_array[$key] = $var;
			} else {
				$row_array[$key] = '0';
			}
			// Some nasty exceptions
			if (!$row_array[$key]) $row_array[$key] = '0'; // Nulify empty keys 
			if ($row_array[0] == '0') $row_array[0] = '00:00:00'; // time value cannot be 0 so we set it in time format. Need to rework

		}

		// Remove empty elements
		$row_array = array_filter($row_array, function($value) { return $value !== ''; });

		// We add array data to a new array only if keys count is above 5 to filter data only (skip 'LINUX RESTART' short row, blank rows etc.)
		if (count($row_array) > 5 ) {
			// Nasty exception for network
			if ($atr != '-n DEV') {
				$out[$key_row] = $row_array;
			} else {
				if (count($row_array) == 18 && $row_array['3'] == "eth0") {
					$out[$key_row] = $row_array;
				}
			}
		}
	}

	// Debug
	//print_r(var_dump($out));

	return $out;
}
