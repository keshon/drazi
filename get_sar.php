<?php
//-------------------
// SAR info parser for CPU, RAM and Network
// The output is an array filled with usage data:
//-------------------

function get_sar_info($atr = 'network') {

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

	$data = shell_exec('LC_TIME=en_UK.utf8 sar ' . $atr);	// execute 'sar' utility with UK locale parameter so the time format is 24H
	$data = explode("\n", $data);				// divide output by lines and refill the array
	$data = array_slice($data, 1); 				// slice the array so we skip header which occurs every 24 hours	

	$out = array(); 					// declare the output array

	// Loop throw data array and remove all spaces between columns
	foreach ($data as $key => $item)
	{
		$data[$key] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $data[$key]);
		$data[$key] = rtrim($data[$key]);
		$data[$key] = explode(" ", $data[$key]);
	}
	
	// Loop throw data array to filter non-data values and send data values to output array
	foreach ($data as $key_row => $row_array)
	{
		// Loop throw each key in a row. Second level
		foreach ($row_array as $key => $var)
		{
			// Filter non-data values like labels, info strings and set them to 0
			// This is an indication if the server was restarted
			if (!preg_match('/All|LINUX|CPU|kb|%/i',$var)) {
				$row_array[$key] = str_replace(' ','',$var);
			} else {
				$row_array[$key] = '0';
			}
		}

		// Remove empty keys
		$row_array = array_filter($row_array, function($value) { return $value !== ''; });

		// Add array data to output array
		// By counting keys in array skip short rows related to restart state
		if (count($row_array) > 3 ) {

			// Skip the 'Average' row that can be seen throw output regularly
			if ($row_array[0] == 'Average:') continue;

			// Nasty exception for network to select the interface we need (eth0 in the example)
			if ($atr != '-n DEV') {
				$out[$key_row] = $row_array;
			} else {
				if ($row_array['1'] == "eth0") $out[$key_row] = $row_array; // The restart state cannot be seen here. Need to fix somehow
			}
		}
	}

	// Pretty looking debug (found on stackoverflow)
	//$pretty = function($v='',$c="&nbsp;&nbsp;&nbsp;&nbsp;",$in=-1,$k=null)use(&$pretty){$r='';if(in_array(gettype($v),array('object','array'))){$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").'<br>';foreach($v as $sk=>$vl){$r.=$pretty($vl,$c,$in+1,$sk).'<br>';}}else{$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").(is_null($v)?'&lt;NULL&gt;':"<strong>$v</strong>");}return$r;};		
	//print_r($pretty($out));

	return $out;
}
