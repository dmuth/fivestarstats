<?php
/**
* This file contains functions that relate to getting vote behavior from
* specific IPs.
*
* @author Douglas Muth <http://www.dmuth.org/>
*/


/**
* Get statistics for most active IPs, and the most number of 1-star votes
*	per IP, to find possible abuse.
*
* @param integer $num The number of top IPs to retrieve.
*
* @return array An associative array of IP information.
*/
function fivestarstats_get_ips($num) {

	$retval = array();

	$retval["top_voters"] = fivestarstats_get_ips_top_voters($num);
	$retval["top_1_star_voters"] = fivestarstats_get_ips_top_1_star_voters($num);
	$retval["lowest_vote_average"] = fivestarstats_get_ips_lowest_vote_average($num);

	return($retval);

} // End of fivestarstats_get_ips()


/**
* Get the top voting IPs.
*
* @param integer $num The number of top IPs to retrieve.
*
* @return array An associative array of IP information.
*/
function fivestarstats_get_ips_top_voters($num) {

	$retval = array();

	$query = "SELECT vote_source, COUNT(*) AS cnt "
		. "FROM "
		. "votingapi_vote "
		. "WHERE "
		. "value_type='percent' "
		. "GROUP BY vote_source "
		. "ORDER BY cnt DESC "
		. "LIMIT $num "
		;
	$cursor = db_query($query);
	while ($row = db_fetch_array($cursor)) {
		$ip = $row["vote_source"];
		$retval[$ip] = $row["cnt"];
	}

	return($retval);

} // End of fivestarstats_get_ips_top_voters()


/**
* Now fetch the top 1-star voters.
* This may or may not pick up abuse. For example, on anthrocon.org, *I*
* am the top 1-star voter, because we really haven't had any 
* "repeat 1-star voters".
*
* @param integer $num The number of top IPs to retrieve.
*
* @return array An associative array of IP information.
*/
function fivestarstats_get_ips_top_1_star_voters($num) {

	$retval = array();

	$query = "SELECT vote_source, COUNT(*) AS cnt "
		. "FROM "
		. "votingapi_vote "
		. "WHERE "
		. "value_type='percent' "
		. "AND value=20 "
		. "GROUP BY vote_source "
		. "ORDER BY cnt DESC "
		. "LIMIT $num "
		;
	$cursor = db_query($query);
	while ($row = db_fetch_array($cursor)) {
		$ip = $row["vote_source"];
		$retval[$ip] = $row["cnt"];
	}

	return($retval);

} // End of fivestarstats_get_ips_top_1_star_voters()


/**
*
* Now determine which IPs have the lowest voting average.
* This is useful to find IPs that have only given low votes.
*
* @param integer $num The number of top IPs to retrieve.
*
* @return array An associative array of IP information.
*/
function fivestarstats_get_ips_lowest_vote_average($num) {

	$retval = array();

	$query = "SELECT vote_source, COUNT(value) AS cnt, "
		. "SUM(value) AS sum, AVG(value) AS avg "
		. "FROM "
		. "votingapi_vote "
		. "WHERE "
		. "value_type='percent' "
		. "GROUP BY vote_source "
		//
		// Right now, we're hard coding in a minimum of 10 votes. 
		// I'll probably change this later.
		//
		. "HAVING cnt >= %d "
		. "ORDER BY avg ASC, cnt DESC "
		. "LIMIT $num "
		;
	$query_args = array($num);
	$cursor = db_query($query, $query_args);
	while ($row = db_fetch_array($cursor)) {
		$ip = $row["vote_source"];
		$retval[$ip] = array(
			"cnt" => $row["cnt"],
			"avg" => $row["avg"] / 20,
			);
	}

	return($retval);
		
} // End of fivestarstats_get_ips_lowest_vote_average()


