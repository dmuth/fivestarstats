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
		array("!link" => l("http://github.com/dmuth/fivestarstats", 
			"http://github.com/dmuth/fivestarstats")));

	$retval["info"] = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#collapsed" => true,
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
*
* @return array Array of form elements.
*/
function fivestarstats_form_ips($data) {

	$retval = array(
		"#type" => "fieldset",
		"#collapsible" => true,
		"#title" => t("IP Activity"),
		"#description" => "",
		);

	$content = array();

	//
	// Top voters
	//
	$header = array(t("IP"), t("# Votes Cast"));
	
	$rows = array();
	foreach ($data["top_voters"] as $key => $value) {
		$link = l($value . t(" votes"), 
			"admin/settings/fivestarstats/ip/" . $key);
		$row = array($key, 
			array("data" => $link, "align" => "right"),
			);
		$rows[] = $row;
	}

	$content["top voters"] = "<h2>" . t("Top Voters") . "</h2>";
	$content["top voters"] .= theme("table", $header, $rows);


	//
	// Top 1-star voters
	//
	$rows = array();
	foreach ($data["top_1_star_voters"] as $key => $value) {
		$link = l($value . t(" votes"), 
			"admin/settings/fivestarstats/ip/" . $key . "/votes/1");
		$row = array($key, 
			array("data" => $link, "align" => "right"),
			);
		$rows[] = $row;
	}

	$content["top 1-star"] = "<h2>" . t("Top 1-star Voters") . "</h2>";
	$content["top 1-star"] .= theme("table", $header, $rows);

	//
	// Lowest vote average
	//
	$header = array(t("IP"), t("# Votes Cast"), t("Average Vote"));
	$rows = array();
	foreach ($data["lowest_vote_average"] as $key => $value) {
		$link = l($value["cnt"] . t(" votes"), 
			"admin/settings/fivestarstats/ip/" . $key);
		$row = array($key, 
			array("data" => $link, "align" => "right"),
			array("data" => sprintf("%.2f", $value["avg"]) . t(" stars"), "align" => "right"),
			);
		$rows[] = $row;
	}

	$content["lowest"] = "<h2>" . t("Lowest Vote Average") . "</h2>";
	$content["lowest"] .= theme("table", $header, $rows);

	$header = array();
	$rows = array();
	$rows[] = array($content["top voters"], $content["top 1-star"], $content["lowest"]);
	$retval["#description"] = theme("table", $header, $rows);

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
		"#title" => t("User Activity (votes received)"),
		"#description" => "",
		);

	$content = array();

	//
	// Top-rated users
	//
	$header = array(t("Username"), t("# of times voted on"), t("Average Vote"));
	$rows = array();
	foreach ($data["top_rated"] as $key => $value) {
		$link = l($value["num_votes"] . t(" votes"), 
			"admin/settings/fivestarstats/uid/" . $value["uid"]);
		$row = array($value["name"], 
			array("data" => $link, "align" => "right"),
			array("data" => sprintf("%.2f", $value["average"]) . t(" stars"),
				"align" => "right"),
			);
		$rows[] = $row;
	}

	$content["top rated"] .= "<h2>" . t("Top-rated Users") . "</h2>";
	$content["top rated"] .= theme("table", $header, $rows);

	//
	// Bottom-rated users
	//
	$rows = array();
	foreach ($data["bottom_rated"] as $key => $value) {
		$link = l($value["num_votes"] . t(" votes"), 
			"admin/settings/fivestarstats/uid/" . $value["uid"]);
		$row = array($value["name"], 
			array("data" => $link, "align" => "right"),
			array("data" => sprintf("%.2f", $value["average"]) . t(" stars"),
				"align" => "right"),
			);
		$rows[] = $row;
		
	}

	$content["bottom rated"] .= "<h2>" . t("Bottom-rated Users") . "</h2>";
	$content["bottom rated"] .= theme("table", $header, $rows);

	//
	// Most 1-star votes received.
	//
	$header = array(t("Username"), t("# of 1-star votes received"));

	$rows = array();
	foreach ($data["most_1_star_votes"] as $key => $value) {
		$link = l($value["cnt"] . t(" votes"), 
			"admin/settings/fivestarstats/uid/" . $value["uid"]);
		$row = array($value["name"], 
			array("data" => $link, "align" => "right"),
			);
		$rows[] = $row;
	}


	$content["1-star"] .= "<h2>" . t("Most 1-star votes received") . "</h2>";
	$content["1-star"] .= theme("table", $header, $rows);

	//
	// Top Posters
	//
	$header = array(t("Username"), t("# posts"), t("# votes received"), 
		t("average rating"));
	$rows = array();
	foreach ($data["top_posters"] as $key => $value) {
		$link = l($value["num_votes"] . t(" votes"), 
			"admin/settings/fivestarstats/uid/" . $value["uid"]);
		$row = array($value["name"], 
			array("data" => $value["num_posts"] . t(" posts"), 
				"align" => "right"),
			array("data" => $link, "align" => "right"),
			array("data" => sprintf("%.2f stars", $value["average"]), 
				"align" => "right"),
			);
		$rows[] = $row;
	}


	$content["top posts"] .= "<h2>" . t("Top posts by # posts/comments") . "</h2>";
	$content["top posts"] .= theme("table", $header, $rows);

	$header = array();
	$rows = array();
	$rows[] = array($content["top rated"], $content["top posts"]);
	$rows[] = array($content["1-star"], $content["bottom rated"]);

	$retval["#description"] = theme("table", $header, $rows);

	return($retval);

} // End of fivestarstats_form_users()


