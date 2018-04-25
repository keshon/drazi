<?php
//
// SAR CPU info parser
// The output is an array filled with CPU usage data (and some empty keys):
// out[0] Time (timestamp for the record)
// out[1] 
// out[2] 
// out[3] CPU  (read sar docs)
// out[4] 
// out[5] %user (read sar docs)
// out[6] 
// out[7] %nice (read sar docs)
// out[8] 
// out[9] %system (read sar docs)
// out[10] 
// out[11] %iowait (read sar docs)
// out[12] 
// out[13] %steal (read sar docs)
// out[14] 
// out[15] %idle (read sar docs)

function sar_cpu_history() {

	$data = shell_exec('LC_TIME=en_UK.utf8 sar'); //execute 'sar' utility with UK locale parameter so the time format is 24H
	$data = explode("\n", $data); // divide output by lines and refill the array
	$data = array_slice($data, 3, -2); // offset the array so we skip header and footer	

	$out = array(); // declare the output array

	// Loop throw array and remove all spaces possible. Only data is allowed
	foreach ($data as $key => $item)
	{
		$data[$key] = preg_split( "/ ( |  |   |    |     |      |) /", $item );
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
			if ($row_array[0] == '0') $row_array[0] = '00:00:00'; // data value cannot be 0 so we set it in time format. Need to rework
			if (!isset($row_array[15])) $row_array[15] = '0'; // key 15 is for CPU idle - set to 0 if it's null 

		}

		// We add array data to a new array only if keys count is above 5 to filter data only (skip 'LINUX RESTART' short row, blank rows etc.)
		if (count($row_array) > 5 ) {
			$out[$key_row] = $row_array;
		}

	}

	// Debug
	//print_r(var_dump($out));

	return $out;
}
