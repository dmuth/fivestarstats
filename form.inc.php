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

	//
	// Blurb at the top of the page with module info.
	//
	$content = "";
	$dirname = drupal_get_filename("module", "fivestarstats");
	$filename = dirname($dirname) . "/README.txt";
	$content .= t("The module README !link", 
		array("!link" => l(t("can be read here"), $filename))) 
		. ".<br/>";
	$content .= t("The latest version of this module can be downloaded from: !link.<br/>",
		array("!link" => l("http://github.com/dmuth/fivestarstats", "http://github.com/dmuth/fivestarstats")));

	$retval["info"] = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("Module Info"),
		"#description" => $content,
		);

	$retval["total_votes"] = fivestarstats_form_total_votes($data["total"]);
	$retval["ips"] = fivestarstats_form_ips($data["ips"]);
	$retval["users"] = fivestarstats_form_users($data["users"]);

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
		"#value" => t("%num_votes votes from %num_ips unique IPs "
			. "with average of %num_stars stars per vote.",
			array("%num_votes" => number_format($data["num_votes"]),
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
		$row = array($key, 
			array("data" => $value . t(" votes"), "align" => "right"),
			);
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
		$row = array($key, 
			array("data" => $value . t(" votes"), "align" => "right"),
			);
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
		$row = array($key, 
			array("data" => $value["cnt"] . t(" votes"), "align" => "right"),
			array("data" => sprintf("%.2f", $value["avg"]) . t(" stars"), "align" => "right"),
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


/**
* Create form elements with user info.
*/
function fivestarstats_form_users($data) {

	$retval = array();

	$retval = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("User Activity"),
		);

	//
	// Top-rated users
	//
	$header = array(t("Username"), t("# of times voted on"), t("Average Vote"));
	$rows = array();
	foreach ($data["top_rated"] as $key => $value) {
		$row = array($value["name"], 
			array("data" => $value["num_votes"], "align" => "right"),
			array("data" => sprintf("%.2f", $value["average"]) . t(" stars"),
				"align" => "right"),
			);
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["top_rated"] = array(
		"#type" => "fieldset",
		"#title" => t("Top-rated Users"),
		"#description" => $content,
		);
	
	//
	// Bottom-rated users
	//
	$rows = array();
	foreach ($data["bottom_rated"] as $key => $value) {
		$row = array($value["name"], 
			array("data" => $value["num_votes"], "align" => "right"),
			array("data" => sprintf("%.2f", $value["average"]) . t(" stars"),
				"align" => "right"),
			);
		$rows[] = $row;
		
	}

	$content = theme("table", $header, $rows);

	$retval["bottom_rated"] = array(
		"#type" => "fieldset",
		"#title" => t("Bottom-rated Users"),
		"#description" => $content,
		);

	//
	// Most 1-star votes received.
	//
	$header = array(t("Username"), t("# of 1-star votes received"));

	$rows = array();
	foreach ($data["most_1_star_votes"] as $key => $value) {
		$row = array($value["name"], 
			array("data" => $value["cnt"] . t(" votes"),
				"align" => "right")
			);
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["most_1_star_votes"] = array(
		"#type" => "fieldset",
		"#title" => t("Most 1-star votes received"),
		"#description" => $content,
		);


	//
	// Top Posters
	//
	$header = array(t("Username"), t("# posts"), t("# votes received"), 
		t("average rating"));
	$rows = array();
	foreach ($data["top_posters"] as $key => $value) {
		$row = array($value["name"], 
			array("data" => $value["num_posts"] . t(" posts"), 
				"align" => "right"),
			array("data" => $value["num_votes"] . t(" votes"), 
				"align" => "right"),
			array("data" => sprintf("%.2f stars", $value["average"]), 
				"align" => "right"),
			);
		$rows[] = $row;
	}

	$content = theme("table", $header, $rows);

	$retval["top_posters"] = array(
		"#type" => "fieldset",
		"#title" => t("Top posters by # posts/comments"),
		"#description" => $content,
		);

	return($retval);

} // End of fivestarstats_form_users()
