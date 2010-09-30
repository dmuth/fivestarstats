<?php
/**
* This file holds functions that get data on voting patterns of specific IPs.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Get votes that were cost by this IP.
*
* @param string $ip The IP address
*
* @return array An array of votes cast by star
*/
function fivestarstats_ip_get_votes($ip) {

	$retval = array();

	$query = "SELECT value, COUNT(*) AS cnt "
		. "FROM "
		. "votingapi_vote "
		. "WHERE "
		. "vote_source='%s' "
		. "AND (content_type='node' OR content_type='comment') "
		. "AND value_type='percent' "
		. "GROUP by value "
		;
	$query_args = array($ip);
	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$value = $row["value"] / 20;
		$retval[$value] = $row["cnt"];
	}

	//
	// Put highest rated votes first
	//
	krsort($retval);

	return($retval);

} // End of fivestarstats_ip_get_votes()


/**
* Get users that used this IP
*
* @param string $ip The IP address
*
* @return array An array of users who used this IP.
*/
function fivestarstats_ip_get_users($ip) {

	$retval = array();

	$query = "SELECT "
		. "accesslog.uid, users.name, COUNT(*) AS cnt "
		. "FROM "
		. "accesslog "
		. "JOIN users ON users.uid = accesslog.uid "
		. "WHERE "
		. "accesslog.hostname=%d "
		. "GROUP BY accesslog.uid "
		. "ORDER BY cnt DESC "
		;
	$query_args = array($ip);

	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats-ip_get_users()


/**
* Get HTML of the vote distribution for an IP.
*
* @param string $ip The IP address
*
* @param array $data Array of vote values and quantities.
*
* @return string HTML code.
*/
function fivestarstats_ip_get_votes_html($ip, $data) {

	$retval = "";

	$retval .= t("<h2>Votes cast by IP: %ip</h2>", array("%ip" => $ip));

	$header = array(t("Stars"), t("Number of Votes cast"));
	$rows = array();

	$num_votes = 0;
	foreach ($data as $key => $value) {
		$link = l($value . t(" votes"), 
			"admin/settings/fivestarstats/ip/$ip/votes/$key");
		$row = array($key . t(" stars"),
			array("data" => $link, "align" => "right"),
			);
		$rows[]  = $row;
		$num_votes += $value;
	}

	if (empty($data)) {
		$rows[] = array(
			array("data" => t("No votes found from this IP"), "colspan" => "2")
			);

	} else {
		$rows[] = array(
			t("All Ratings"),
			array("data" => l(t("!num_votes votes", array("!num_votes" => $num_votes)),
					"admin/settings/fivestarstats/ip/$ip/votes/all"
					),
				"align" => "right"),
			);

	}

	$retval .= theme("table", $header, $rows);

	return($retval);

} // End of fivestarstats_ip_get_votes_html()


/**
* Get HTML of the users from this IP.
*
* @param array $data Array of users from this IP.
*
* @param string $ip The IP address
*
* @return string HTML code.
*/
function fivestarstats_ip_get_users_html($data) {

	$retval = "";

	$header = array(t("Username"), t("# of accesses from this IP"));
	$rows = array();

	foreach  ($data as $key => $value) {
		$uid = $value["uid"];
		$name = $value["name"];

		if ($uid != 0) {
			$row = array(l($name, "user/" . $uid), 
				array("data" => $value["cnt"] . t(" times"), "align" => "right")
				);

		} else {
			$row = array(t("Anonymous User"), 
				array("data" => $value["cnt"] . t(" times"), "align" => "right")
				);

		}

		$rows[] = $row;

	}

	if (empty($data)) {
		$rows[] = array(
			array("data" => t("No users found from this IP"), "colspan" => "2")
			);
	}

	$retval .= theme("table", $header, $rows);

	return($retval);

} // End of fivestarstats_ip_get_users_html()


/**
* Get all votes cast by a specific IP.
*
* @param string $ip The IP address
*
* @param mixed $num_stars The number of stars, or "all" for all votes.
*
* @return array An array of all votes cast, in reverse chronological order.
*/
function fivestarstats_ip_get_votes_detail($ip, $num_stars) {

	$retval = array();

	$query = "SELECT "
		. "votingapi_vote.uid, "
		. "users.name, "
		//
		// Get our NID for nodes and comments.
		//
		. "IF(node.nid, node.nid, comments.nid) AS nid, "
		. "node.title AS node_title, "
		. "comments.cid, "
		. "comments.subject AS comment_title, "
		. "votingapi_vote.value, votingapi_vote.timestamp "
		. "FROM votingapi_vote "
		. "LEFT JOIN users ON (users.uid=votingapi_vote.uid) "
		. "LEFT JOIN node ON (node.nid=votingapi_vote.content_id AND content_type='node') "
		. "LEFT JOIN comments ON (comments.cid=votingapi_vote.content_id AND content_type='comment') "
		. "WHERE "
		. "vote_source='%s' "
		. "AND content_type IN ('node', 'comment') "
		. "AND value_type='percent' "
		;
	$query_args = array($ip);
	if ($num_stars != "all") {
		$query .= "AND value=%d ";
		$query_args[] = $num_stars * 20;
	}

	$query .= "ORDER BY votingapi_vote.timestamp DESC ";

	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$row["timestamp"] = format_date(
			$row["timestamp"], "custom", "Y-m-d H:h:s a");
		$row["rating"] = $row["value"] / 20;

		$retval[] = $row;
	}

	return($retval);

} // End of fivestarstats_ip_get_votes()


/**
* Get HTML to display the votes detail for a specific IP
*
* @param array $data Array of votes cast for this IP.
*
* @return string HTML code
*/
function fivestarstats_ip_get_votes_detail_html($data) {

	$retval = "";

	$header = array(t("Date"), t("Title"), t("Voter"), t("Rating"));
	$rows = array();

	foreach ($data as $key => $value) {

		$maxlen = 60;

		if (!empty($value["comment_title"])) {
			$title = $value["comment_title"];
			$title = truncate_utf8($title, $maxlen);
			$nid = $value["nid"];
			$cid = $value["cid"];
			$options = array("fragment" => "comment-" . $cid);
			$title = l(t("Comment: ") . $title, 
				"node/" . $nid, $options);

		} else {
			$title = $value["node_title"];
			$title = truncate_utf8($title, $maxlen);
			$nid = $value["nid"];
			$title = l($title, "node/" . $nid);

		}

		if (!empty($value["uid"])) {
			$user = l($value["name"], "user/" . $value["uid"]);

		} else {
			$user = t("Anonymous");

		}

		$row = array();
		$row[] = array("data" => $value["timestamp"], "align" => "right");
		$row[] = $title;
		$row[] = $user;
		$row[] = $value["rating"] . t(" stars");
		$rows[] = $row;
	}

	$retval .= theme("table", $header, $rows);

	return($retval);

} // End of function fivestarstats_ip_get_votes_detail_html()



