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

	//
	// The README is in Markdown format, so filter it, then put it in code tags.
	// The style element is because the default code font size in Drupal is 
	// way too small.
	//
	$info = check_markup($info);

	$retval = "<code style=\"font-size: medium; \" >" 
		. $info 
		. "</code>"
		;

	return($retval);

} // End of fivestarstats_about()



