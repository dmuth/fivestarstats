<?php
/**
* Our form-related code.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Our form callback.
*
* @param array $form_data Form data
*
* @param array $data Our array of data that we passed in
*
* @return array An array of form elements for the form generator.
*/
function fivestarstats_form($form_data, $data) {

	$retval = array();

	$retval["total_votes"] = fivestarstats_form_total_votes($data["total"]);
	$retval["ips"] = fivestarstats_form_ips($data["ips"]);

	return($retval);

} // End of fivestarstats_form()


/**
* Create form elements for the total vote data.
*
* @param array $data Our array of data that we passed in
*
* @return array An array of form elements for the form generator.
*/
function fivestarstats_form_total_votes($data) {

	$retval = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("Total Votes"),
		);

	$retval["content"] = array(
		"#type" => "item",
		"#value" => t("%num_votes from %num_ips unique IPs "
			. "with average of %num_stars stars per vote.",
			array("%num_votes" => $data["num_votes"],
				"%num_ips" => $data["num_ips"],
				"%num_stars" => $data["average_stars"])
			)
		);

	return($retval);

} // End of fivestarstats_form_total_values()


/**
* Create form elements for top IP addresses.
*/
function fivestarstats_form_ips($data) {

	$retval = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("IP Activity"),
		);

	//
	// Top voters
	//
	$header = array(t("IP"), t("Number of votes"));
	
	$rows = array();
	foreach ($data["top_voters"] as $key => $value) {
		$row = array($key, $value . t(" votes"));
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["top_voters"] = array(
		"#type" => "fieldset",
		"#title" => t("Top Voters"),
		"#description" => $content,
		);

	//
	// Top 1-star voters
	//
	$rows = array();
	foreach ($data["top_1_star_voters"] as $key => $value) {
		$row = array($key, $value . t(" votes"));
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["top_1_star_voters"] = array(
		"#type" => "fieldset",
		"#title" => t("Top 1-star Voters"),
		"#description" => $content,
		);

	//
	// Lowest vote average
	//
	$header = array(t("IP"), t("Number of Votes"), t("Average Vote"));
	$rows = array();
	foreach ($data["lowest_vote_average"] as $key => $value) {
		$row = array($key, $value["cnt"] . t(" votes"), 
			sprintf("%.2f", $value["avg"]) . t(" stars")
			);
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["lowest_vote_average"] = array(
		"#type" => "fieldset",
		"#title" => t("Lowest Vote Average"),
		"#description" => $content,
		);

	return($retval);

} // End of fivestarstats_form_ips()



