<?php
/**
* This file holds functions for our "about" tab.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Our "about" tab.
*
* @return string HTML code.
*/
function fivestarstats_about() {

	$retval = "";

	$dir = drupal_get_path("module", "fivestarstats");
	$file = "$dir/README.md";

	$fp = fopen($file, "r");

	$info = "";
	while ($line = fread($fp, 1024)) {
		$info .= $line;
	}

	fclose($fp);

	$retval .= "<pre>" . $info . "</pre>";

	return($retval);

} // End of fivestarstats_about()



