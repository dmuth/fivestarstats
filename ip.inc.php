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


