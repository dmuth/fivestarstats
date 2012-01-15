<?php
/**
* This file holds our tools code.
*
* @author Douglas Muth <http://www.dmuth.org/">
*
*/


/**
* Our main tools page.
*
* @return string HTML code
*/
function fivestarstats_tools() {

	$retval = "";

	$data = fivestarstats_tools_get_data();

	$retval .= drupal_get_form("fivestarstats_tools_form", $data);

	return($retval);

} // End of fivestarstats_tools()


/**
* Get some basic data on our votes cast.
*
* @return array An array of data
*/
function fivestarstats_tools_get_data() {

	$retval = array();

	$query = "SELECT content_type, COUNT(vote_id) AS cnt "
		. "FROM {votingapi_vote} "
		. " GROUP BY content_type "
		. "ORDER BY content_type "
		;
	$cursor = db_query($query);
	while ($row = db_fetch_array($cursor)) {

		$type = $row["content_type"];
		$cnt = $row["cnt"];

		$retval[$type] = $cnt;

	}

	return($retval);

} // End of fivestarstats_tools_get_data()


/**
* Our form for recalculating vote totals.
*
* @param array $data Our array of vote data
*
* @return array An array of form data
*/
function fivestarstats_tools_form($form_data, $data) {

	$retval = array();

	//
	// Our outer fieldset.
	//
	$info = array();
	$info["#title"] = "Vote Info";
	$info["#type"] = "fieldset";
	$retval["info"] = $info;

	//
	// Create our table and put it in our fieldset.
	//
	$headers = array("Type", "# Votes");
	$rows = array();

	foreach ($data as $key => $value) {

		$row = array();
		$row[] = $key;
		$row[] = $value;

		$rows[] = $row;

	}

	$info_data = array();
	$info_data = array();
	$info_data["#type"] = "item";
	$info_data["#value"] = print_r($data, true);
	$info_data["#value"] = theme("table", $headers, $rows);
	$retval["info"]["data"] = $info_data;
	
	//
	// Our submit button.
	//
	$submit = array();
	$submit["#type"] = "submit";
	$submit["#value"] = "Re-calculate vote totals";
	$retval["info"]["submit"] = $submit;
	
	$warning = array();
	$warning["#type"] = "item";
	$warning["#description"] = "WARNING: This is database-intensive and make take some time!"
		;
	$retval["info"]["warning"] = $warning;

	return($retval);

} // End of fivestarstats_tools_form()


function fivestarstats_tools_form_validate($form, $form_state) {
}


/**
* Our form submission handler.
*/
function fivestarstats_tools_form_submit($form, $form_state) {

    $values = $form_state["values"];

	$time_start = microtime(true);

	//
	// Select all of our unique voted on items, and then recalculate 
	// the totals for each.
	//
	$query = "SELECT DISTINCT content_type, content_id "
		. "FROM {votingapi_vote}"
		;
	$cursor = db_query($query);
	$time_query = microtime(true);

	$num = 0;
	while ($row = db_fetch_array($cursor)) {
		$type = $row["content_type"];
		$id = $row["content_id"];

		votingapi_recalculate_results($type, $id, true);

		$num++;

	}

	$time_done = microtime(true);

	$time_diff_query = $time_query - $time_start;
	$message = sprintf("Query made in %.3f seconds", $time_diff_query);
	drupal_set_message($message);
	watchdog("fivestarstats", $message, "", WATCHDOG_NOTICE);

	$time_diff_update = $time_done - $time_query;
	$message = sprintf("%d items updated in %.3f seconds", $num, 
		$time_diff_update);
	drupal_set_message($message);
	watchdog("fivestarstats", $message, "", WATCHDOG_NOTICE);

} // End of fivestarstats_tools_form_submit()


