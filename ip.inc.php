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

	foreach ($data as $key => $value) {
		$link = l($value . t(" votes"), "fivestarstats/ip/$ip/$key");
		$row = array($key . t(" stars"),
			array("data" => $link, "align" => "right"),
			);
		$rows[]  = $row;
	}

	if (empty($data)) {
		$rows[] = array(
			array("data" => t("No votes found from this IP"), "colspan" => "2")
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



